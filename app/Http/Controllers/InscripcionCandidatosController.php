<?php

namespace App\Http\Controllers;

use App\Models\AuditoriaPostulacionCandidato;
use App\Models\Eventos;
use App\Models\Funcionarios_planeacion;
use App\Models\Hash_votantes;
use App\Models\Informacion_votantes;
use App\Models\Postulaciones_candidatos;
use App\Models\User;
use App\Services\CentralDataService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Inertia\Inertia;

class InscripcionCandidatosController extends Controller
{
    protected CentralDataService $centralDataService;

    /**
     * Configuración de permisos específicos para el validador de candidatos y servicios
     */
    public function __construct(CentralDataService $centralDataService)
    {
        $this->centralDataService = $centralDataService;
        $this->middleware('permission:validador-candidatos-listar', ['only' => ['index']]);
        $this->middleware('permission:validador-candidatos-aprobar', ['only' => ['aprobar']]);
        $this->middleware('permission:validador-candidatos-rechazar', ['only' => ['rechazar']]);
        $this->middleware('permission:validador-candidatos-historial', ['only' => ['historial']]);
    }
    /**
     * Muestra el formulario público de inscripción de candidatos.
     * Solo permite seleccionar eventos en estado 'Pendiente'.
     */
    public function formulario()
    {
        // Buscar eventos en estado Pendiente
        $eventosPendientes = Eventos::where('estado', 'Pendiente')
            ->where('nombre', 'not like', '%Admin%')
            ->orderBy('id', 'desc')
            ->get(['id', 'nombre', 'descripcion', 'fecha_inicio', 'fecha_fin']);

        $hoy = Carbon::now();

        return Inertia::render('InscripcionCandidatos/Formulario', [
            'eventos_pendientes' => $eventosPendientes,
            'fecha_actual' => [
                'completa' => $hoy->format('Y-m-d'),
                'dia' => $hoy->format('d'),
                'mes' => $hoy->format('m'),
                'anio' => $hoy->format('Y'),
                'texto' => $hoy->translatedFormat('d \d\e F \d\e Y'),
            ],
            'titulo_encabezado' => 'SECRETARÍA DE GESTIÓN ADMINISTRATIVA',
            'subtitulo_encabezado' => 'DIRECCIÓN ADMINISTRATIVA DE TALENTO HUMANO',
            'nombre_formulario' => 'FORMULARIO DE INSCRIPCIÓN CANDIDATOS – COMISIÓN DE PERSONAL',
        ]);
    }

    /**
     * Consulta el número de documento para auto-llenar los datos del funcionario.
     * Solo retorna datos si el usuario es funcionario (is_funcionario == 1 o true).
     */
    public function consultarFuncionario(Request $request)
    {
        $request->validate([
            'identificacion' => 'required|string|max:30',
            'tipo_documento' => 'nullable|string|max:20',
            'id_evento' => 'nullable|integer',
        ]);

        $identificacion = trim($request->identificacion);
        $tipoDocSeleccionado = $request->input('tipo_documento', 'CC');

        // 1. Buscar en Informacion_votantes (tabla votantes)
        $votante = Informacion_votantes::where('identificacion', $identificacion)->first();

        // 2. Buscar en funcionarios_planeacion si existe
        $funcionarioPlaneacion = null;
        if (class_exists(Funcionarios_planeacion::class)) {
            $funcionarioPlaneacion = Funcionarios_planeacion::where('identificacion', $identificacion)->first();
        }

        $esFuncionario = false;
        $nombre = null;
        $tipoDoc = $tipoDocSeleccionado ?: ($votante && $votante->tipo_documento ? $votante->tipo_documento : 'CC');
        $email = null;
        $celular = null;
        $dependencia = null;
        $provision = null;
        $planta = null;
        $tipoPlanta = 'PLANTA CENTRAL';

        if ($votante) {
            $nombre = $votante->nombre;
            if (empty($tipoDocSeleccionado) && !empty($votante->tipo_documento)) {
                $tipoDoc = $votante->tipo_documento;
            }
            $email = $votante->email;
            $celular = $votante->celular;
            $dependencia = $votante->dependencia;
            $provision = $votante->provision;
            $planta = $votante->planta;

            if (!empty($planta)) {
                $plantaUpper = strtoupper(trim($planta));
                if (in_array($plantaUpper, ['SGP', 'PLANTA CENTRAL'])) {
                    $tipoPlanta = $plantaUpper;
                }
            }

            // Verificar si es funcionario:
            // a) is_funcionario == 1 / true
            // b) campo relacion contiene funcionario / planta central / sgp / carrera
            // c) o existe en funcionarios_planeacion
            if (isset($votante->is_funcionario) && ($votante->is_funcionario == 1 || $votante->is_funcionario === true || $votante->is_funcionario === '1')) {
                $esFuncionario = true;
            } elseif (!empty($votante->relacion) && in_array(mb_strtolower(trim($votante->relacion)), ['funcionario', 'planta central', 'sgp', 'carrera administrativa'])) {
                $esFuncionario = true;
                if (stripos($votante->relacion, 'sgp') !== false) {
                    $tipoPlanta = 'SGP';
                }
            } elseif ($funcionarioPlaneacion) {
                $esFuncionario = true;
            }
        } elseif ($funcionarioPlaneacion) {
            $esFuncionario = true;
            $nombre = $funcionarioPlaneacion->nombre;
            $dependencia = $funcionarioPlaneacion->area ?? null;
        }

        if (!$esFuncionario) {
            return response()->json([
                'success' => false,
                'encontrado' => false,
                'mensaje' => 'El número de identificación no corresponde a un funcionario público activo habilitado para esta postulación o no se encuentra registrado en el censo oficial de empleados. Si considera que se trata de un error, por favor contacte a la Dirección Administrativa de Talento Humano.',
            ], 422);
        }

        // Si no tenía email o celular en local, consultar en Central Data para auto-completar y no perder datos
        if (empty($email) || empty($celular)) {
            try {
                $tipoDocCode = $this->centralDataService->getTipoDocumentoDataCenter($tipoDoc) ?? 'CC';
                $findCentral = $this->centralDataService->findPerson($tipoDocCode, $identificacion, 'votaciones');
                if (!empty($findCentral['ok']) && !empty($findCentral['data'])) {
                    $pData = isset($findCentral['data']['data']) && is_array($findCentral['data']['data'])
                        ? $findCentral['data']['data']
                        : (is_array($findCentral['data']) ? $findCentral['data'] : []);
                    if (empty($email) && !empty($pData['correo'])) {
                        $email = $pData['correo'];
                    }
                    if (empty($celular) && !empty($pData['telefono'])) {
                        $celular = $pData['telefono'];
                    }
                }
            } catch (\Throwable $e) {
                // Silencioso
            }
        }

        // Verificar si ya tiene una postulación en este evento o en estado Pendiente/Aprobado
        $queryPostulacion = Postulaciones_candidatos::where('identificacion', $identificacion);
        if ($request->filled('id_evento')) {
            $queryPostulacion->where('id_evento', $request->id_evento);
        }
        $postulacionExistente = $queryPostulacion->whereIn('estado', ['Pendiente', 'Aprobado'])->first();

        if ($postulacionExistente) {
            return response()->json([
                'success' => true,
                'encontrado' => true,
                'ya_postulado' => true,
                'radicado' => $postulacionExistente->radicado,
                'fecha_postulacion' => $postulacionExistente->fecha_postulacion,
                'estado' => $postulacionExistente->estado,
                'tipo_planta' => $postulacionExistente->tipo_planta,
                'dependencia' => $postulacionExistente->dependencia ?: ($dependencia ?: 'No registrada'),
                'provision' => $postulacionExistente->provision ?: ($provision ?: 'No registrada'),
                'planta' => $postulacionExistente->planta ?: ($planta ?: $tipoPlanta),
                'mensaje' => "El funcionario ya cuenta con una postulación registrada con radicado {$postulacionExistente->radicado} en estado '{$postulacionExistente->estado}'.",
            ]);
        }

        return response()->json([
            'success' => true,
            'encontrado' => true,
            'ya_postulado' => false,
            'votante_id' => $votante ? $votante->id : null,
            'nombre' => $nombre,
            'identificacion' => $identificacion,
            'tipo_documento' => $tipoDoc,
            'email' => $email,
            'celular' => $celular,
            'dependencia' => $dependencia ?: 'No registrada',
            'provision' => $provision ?: 'No registrada',
            'planta' => $planta ?: $tipoPlanta,
            'tipo_planta' => $tipoPlanta,
        ]);
    }

    /**
     * Guarda la postulación del candidato y procesa la carga de documentos.
     */
    public function store(Request $request)
    {
        $request->validate([
            'identificacion' => 'required|string|max:30',
            'nombre' => 'required|string|max:255',
            'tipo_documento' => 'required|string|max:20',
            'id_evento' => 'required|integer|exists:eventos,id',
            'dependencia' => 'nullable|string|max:255',
            'provision' => 'nullable|string|max:255',
            'planta' => 'nullable|string|max:100',
            'tipo_planta' => 'required|string|in:PLANTA CENTRAL,SGP',
            'email' => 'nullable|email|max:191',
            'celular' => 'nullable|string|max:30',
            'fecha_postulacion' => 'required|date',
            'declaracion' => 'required|accepted',
            'archivo_procuraduria' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'archivo_cedula' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'foto' => 'nullable|file|mimes:jpg,jpeg,png|max:5120',
        ], [
            'id_evento.required' => 'Debe seleccionar el evento de votación para la postulación.',
            'id_evento.exists' => 'El evento de votación seleccionado no es válido.',
            'declaracion.accepted' => 'Debe aceptar la declaración juramentada de cumplimiento de normatividad.',
            'archivo_procuraduria.required' => 'Debe adjuntar el certificado vigente no mayor a tres meses de la Procuraduría.',
            'archivo_procuraduria.mimes' => 'El certificado de Procuraduría debe estar en formato PDF, JPG o PNG.',
            'archivo_cedula.required' => 'Debe adjuntar la fotocopia legible de la cédula de ciudadanía.',
            'archivo_cedula.mimes' => 'La fotocopia de la cédula debe estar en formato PDF, JPG o PNG.',
        ]);

        // Verificar que el evento esté estrictamente en estado Pendiente
        $evento = Eventos::find($request->id_evento);
        if (!$evento || strcasecmp($evento->estado, 'Pendiente') !== 0) {
            return redirect()->back()->with('error', 'El evento seleccionado no se encuentra en estado Pendiente para postulaciones.');
        }

        $identificacion = trim($request->identificacion);

        // Verificar postulación existente en el mismo evento
        $existe = Postulaciones_candidatos::where('identificacion', $identificacion)
            ->where('id_evento', $request->id_evento)
            ->whereIn('estado', ['Pendiente', 'Aprobado'])
            ->first();

        if ($existe) {
            return redirect()->back()->with('error', "Ya existe una inscripción registrada para este documento en este evento con radicado {$existe->radicado} en estado '{$existe->estado}'.");
        }

        DB::beginTransaction();
        try {
            $timestamp = time();
            $safeId = preg_replace('/[^0-9]/', '', $identificacion);

            // 1. Guardar archivo de Procuraduría
            $procExt = $request->file('archivo_procuraduria')->getClientOriginalExtension();
            $procFileName = "procuraduria_{$safeId}_{$timestamp}.{$procExt}";
            $request->file('archivo_procuraduria')->storeAs('uploads/documentos_candidatos', $procFileName, 'public');

            // 2. Guardar archivo de Cédula legible
            $ccExt = $request->file('archivo_cedula')->getClientOriginalExtension();
            $ccFileName = "cedula_{$safeId}_{$timestamp}.{$ccExt}";
            $request->file('archivo_cedula')->storeAs('uploads/documentos_candidatos', $ccFileName, 'public');

            // 3. Guardar foto (opcional)
            $fotoFileName = null;
            if ($request->hasFile('foto')) {
                $fotoExt = $request->file('foto')->getClientOriginalExtension();
                $fotoFileName = "foto_{$safeId}_{$timestamp}.{$fotoExt}";
                // Guardar en uploads/candidatos (expediente de postulación)
                $request->file('foto')->storeAs('uploads/candidatos', $fotoFileName, 'public');
                // Guardar también en uploads/usuarios para compatibilidad total con vistas y tarjetones
                $request->file('foto')->storeAs('uploads/usuarios', $fotoFileName, 'public');
            }

            // Generar número de radicado único
            $radicado = 'CP-' . date('Y') . '-' . strtoupper(Str::random(6));

            $votante = Informacion_votantes::where('identificacion', $identificacion)->first();

            // 1. Modificar/actualizar información en votantes: tipo_documento, email, celular, planta, dependencia, provisión
            if ($votante) {
                if ($request->filled('tipo_documento')) {
                    $votante->tipo_documento = $request->tipo_documento;
                }
                if ($request->filled('email')) {
                    $votante->email = trim($request->email);
                }
                if ($request->filled('celular')) {
                    $votante->celular = trim($request->celular);
                }
                if ($request->filled('tipo_planta')) {
                    $votante->planta = $request->tipo_planta;
                }
                if ($request->filled('dependencia')) {
                    $votante->dependencia = $request->dependencia;
                }
                if ($request->filled('provision')) {
                    $votante->provision = $request->provision;
                }
                if ($fotoFileName) {
                    $votante->imagen = $fotoFileName;
                }
                $votante->is_funcionario = 1;
                $votante->save();
            } else {
                // Si no existía en votantes, consultar si ya existe en Central Data para heredar comuna, barrio, dirección, etc.
                $datosCentrales = null;
                try {
                    $tipoDocCode = $this->centralDataService->getTipoDocumentoDataCenter($request->tipo_documento ?: 'CC') ?? 'CC';
                    $findRes = $this->centralDataService->findPerson($tipoDocCode, $identificacion, 'votaciones');
                    if (!empty($findRes['ok']) && !empty($findRes['data'])) {
                        $datosCentrales = isset($findRes['data']['data']) && is_array($findRes['data']['data'])
                            ? $findRes['data']['data']
                            : (is_array($findRes['data']) ? $findRes['data'] : null);
                    }
                } catch (\Throwable $eFind) {
                    Log::warning('CentralData: no se pudo consultar persona previa al crear votante', ['error' => $eFind->getMessage()]);
                }

                $dirCentral = '';
                if ($datosCentrales && isset($datosCentrales['direccion'])) {
                    $dirCentral = is_array($datosCentrales['direccion'])
                        ? ($datosCentrales['direccion']['via principal'] ?? '')
                        : (string)$datosCentrales['direccion'];
                }

                $votante = Informacion_votantes::create([
                    'nombre' => $request->nombre,
                    'identificacion' => $identificacion,
                    'tipo_documento' => $request->tipo_documento ?: ($datosCentrales['tipo_documento'] ?? 'CC'),
                    'email' => $request->email ? trim($request->email) : ($datosCentrales['correo'] ?? null),
                    'celular' => $request->celular ? trim($request->celular) : ($datosCentrales['telefono'] ?? null),
                    'planta' => $request->tipo_planta,
                    'dependencia' => $request->dependencia,
                    'provision' => $request->provision,
                    'comuna' => $datosCentrales['comuna'] ?? null,
                    'barrio' => $datosCentrales['barrio'] ?? null,
                    'direccion' => $dirCentral ?: null,
                    'nacimiento' => $datosCentrales['fecha_nacimiento'] ?? null,
                    'genero' => $datosCentrales['genero'] ?? null,
                    'etnia' => $datosCentrales['etnia'] ?? null,
                    'condicion' => $datosCentrales['condicion'] ?? null,
                    'is_funcionario' => 1,
                    'imagen' => $fotoFileName ?: 'user.png',
                ]);
            }

            // Crear registro de postulación
            $postulacion = Postulaciones_candidatos::create([
                'id_votante' => $votante ? $votante->id : null,
                'id_evento' => $request->id_evento,
                'identificacion' => $identificacion,
                'tipo_documento' => $request->tipo_documento ?: 'CC',
                'nombre' => $request->nombre,
                'email' => $request->email ? trim($request->email) : ($votante ? $votante->email : null),
                'celular' => $request->celular ? trim($request->celular) : ($votante ? $votante->celular : null),
                'dependencia' => $request->dependencia ?: ($votante ? $votante->dependencia : null),
                'provision' => $request->provision ?: ($votante ? $votante->provision : null),
                'planta' => $request->planta ?: ($votante ? $votante->planta : $request->tipo_planta),
                'tipo_planta' => $request->tipo_planta,
                'fecha_postulacion' => $request->fecha_postulacion,
                'cumple_requisitos' => true,
                'archivo_procuraduria' => $procFileName,
                'archivo_cedula' => $ccFileName,
                'foto' => $fotoFileName,
                'estado' => 'Pendiente',
                'radicado' => $radicado,
            ]);

            DB::commit();

            // 2. Sincronizar datos con Data Center (CENTRAL_DATA_URL) de forma tolerante a fallos
            if ($votante) {
                try {
                    $syncResult = $this->centralDataService->syncVotante($votante, $request->email, $request->celular);
                    if (isset($syncResult['ok']) && $syncResult['ok']) {
                        Log::info('CentralData: candidato sincronizado exitosamente', [
                            'identificacion' => $identificacion,
                            'radicado' => $radicado,
                        ]);
                    } else {
                        Log::warning('CentralData: sincronización no confirmada al inscribir candidato', [
                            'identificacion' => $identificacion,
                            'error' => $syncResult['error'] ?? 'Error desconocido',
                            'status' => $syncResult['status'] ?? null,
                        ]);
                    }
                } catch (\Throwable $eSync) {
                    Log::error('CentralData: excepción sincronizando candidato en Central Data', [
                        'identificacion' => $identificacion,
                        'exception' => $eSync->getMessage(),
                    ]);
                }
            }

            return redirect()->back()->with('success_postulacion', [
                'radicado' => $radicado,
                'nombre' => $request->nombre,
                'identificacion' => $identificacion,
                'evento' => $evento->nombre,
                'tipo_planta' => $request->tipo_planta,
                'fecha' => $request->fecha_postulacion,
                'mensaje' => "Su postulación para '{$evento->nombre}' ha sido radicada con éxito con radicado {$radicado}. Se encuentra en etapa de validación por parte del equipo de talento humano.",
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error registrando postulación de candidato: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Ocurrió un error al procesar la postulación: ' . $e->getMessage());
        }
    }

    /**
     * Panel administrativo de validación y aprobación de postulaciones de candidatos.
     * Solo para rol 'validador-candidatos' o 'Administrador'.
     */
    public function index(Request $request)
    {
        $this->verificarPermisoValidador('validador-candidatos-listar');

        $estado = $request->input('estado', 'Pendiente');
        $idEvento = $request->input('id_evento');
        $search = $request->input('search');
        $planta = $request->input('planta');

        // Métricas
        $totalPostulaciones = Postulaciones_candidatos::count();
        $totalPendientes = Postulaciones_candidatos::where('estado', 'Pendiente')->count();
        $totalAprobadas = Postulaciones_candidatos::where('estado', 'Aprobado')->count();
        $totalRechazadas = Postulaciones_candidatos::where('estado', 'Rechazado')->count();

        // Consulta de postulaciones
        $query = Postulaciones_candidatos::with([
            'evento:id,nombre,estado',
            'validador:id,name,email',
            'votante:id,nombre,identificacion,email,celular,dependencia,provision,planta'
        ]);

        if ($estado && $estado !== 'Todos') {
            $query->where('estado', $estado);
        }

        if ($idEvento) {
            $query->where('id_evento', $idEvento);
        }

        if ($planta) {
            $query->where('tipo_planta', $planta);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                    ->orWhere('identificacion', 'like', "%{$search}%")
                    ->orWhere('radicado', 'like', "%{$search}%")
                    ->orWhere('dependencia', 'like', "%{$search}%");
            });
        }

        $postulaciones = $query->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        $eventos = Eventos::orderBy('id', 'desc')->get(['id', 'nombre', 'estado']);

        return Inertia::render('InscripcionCandidatos/Index', [
            'postulaciones' => $postulaciones,
            'filters' => [
                'estado' => $estado,
                'id_evento' => $idEvento,
                'search' => $search,
                'planta' => $planta,
            ],
            'metrics' => [
                'total' => $totalPostulaciones,
                'pendientes' => $totalPendientes,
                'aprobadas' => $totalAprobadas,
                'rechazadas' => $totalRechazadas,
            ],
            'eventos' => $eventos,
            'motivos_estandar' => [
                'Certificado de la Procuraduría no vigente o mayor a 3 meses',
                'Copia de documento de identidad ilegible o incompleta',
                'No cumple con la condición de empleado de carrera administrativa',
                'Presenta sanciones disciplinarias vigentes en el último año',
                'No pertenece a la planta de personal del evento seleccionado',
                'Inconsistencia en los datos personales suministrados',
                'Documentación adulterada o no auténtica',
                'Otro motivo (especificado en observaciones)',
            ],
        ]);
    }

    /**
     * Aprueba la postulación de un candidato y lo asigna al evento de votación.
     */
    public function aprobar(Request $request, $id)
    {
        $this->verificarPermisoValidador('validador-candidatos-aprobar');

        $postulacion = Postulaciones_candidatos::findOrFail($id);

        if ($postulacion->estado === 'Aprobado') {
            return redirect()->back()->with('error', 'Esta postulación ya se encuentra aprobada.');
        }

        DB::beginTransaction();
        try {
            // 1. Actualizar postulación
            $postulacion->estado = 'Aprobado';
            $postulacion->validador_id = Auth::id();
            $postulacion->fecha_validacion = Carbon::now();
            if ($request->filled('observaciones')) {
                $postulacion->observaciones = $request->observaciones;
            }
            $postulacion->motivos_rechazo = null;
            $postulacion->save();

            // 2. Asignar como candidato en hash_votantes para el evento
            if ($postulacion->id_votante && $postulacion->id_evento) {
                $hash = Hash_votantes::firstOrNew([
                    'id_evento' => $postulacion->id_evento,
                    'id_votante' => $postulacion->id_votante,
                ]);

                $hash->candidato = 1;
                $hash->estado = 'Activo';
                if (empty($hash->tipo)) {
                    $hash->tipo = $postulacion->tipo_planta ?: 'PLANTA CENTRAL';
                }
                $hash->save();

                // Actualizar votante a candidato y sincronizar foto oficial si fue suministrada
                $votante = Informacion_votantes::find($postulacion->id_votante);
                if ($votante) {
                    if (isset($votante->candidato)) {
                        $votante->candidato = 1;
                    }

                    // Si la postulación cuenta con foto, asignarla como imagen oficial del candidato
                    if ($postulacion->foto) {
                        try {
                            if (Storage::disk('public')->exists('uploads/candidatos/' . $postulacion->foto)) {
                                if (!Storage::disk('public')->exists('uploads/usuarios/' . $postulacion->foto)) {
                                    Storage::disk('public')->copy('uploads/candidatos/' . $postulacion->foto, 'uploads/usuarios/' . $postulacion->foto);
                                }
                            }
                        } catch (\Throwable $eFoto) {
                            Log::warning('Error sincronizando foto de candidato a uploads/usuarios: ' . $eFoto->getMessage());
                        }
                        $votante->imagen = $postulacion->foto;
                    }

                    $votante->save();
                }
            }

            // 3. Registrar en AuditoriaPostulacionCandidato
            AuditoriaPostulacionCandidato::create([
                'postulacion_id' => $postulacion->id,
                'id_votante' => $postulacion->id_votante,
                'id_evento' => $postulacion->id_evento,
                'usuario_id' => Auth::id(),
                'accion' => 'Aprobado',
                'motivos_rechazo' => null,
                'observaciones' => $request->observaciones ?: 'Postulación aprobada tras verificación documental satisfactoria.',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            DB::commit();

            return redirect()->back()->with('success', "La postulación {$postulacion->radicado} de {$postulacion->nombre} ha sido aprobada exitosamente y quedó habilitada como candidato para el evento.");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error aprobando postulación candidato: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al aprobar la postulación: ' . $e->getMessage());
        }
    }

    /**
     * Rechaza la postulación de un candidato y registra los motivos de rechazo en auditoría.
     */
    public function rechazar(Request $request, $id)
    {
        $this->verificarPermisoValidador('validador-candidatos-rechazar');

        $request->validate([
            'motivos_rechazo' => 'required|array|min:1',
            'motivos_rechazo.*' => 'string|max:255',
            'observaciones' => 'nullable|string|max:1000',
        ], [
            'motivos_rechazo.required' => 'Debe seleccionar al menos un motivo de rechazo.',
            'motivos_rechazo.min' => 'Debe seleccionar al menos un motivo de rechazo.',
        ]);

        $postulacion = Postulaciones_candidatos::findOrFail($id);

        DB::beginTransaction();
        try {
            // 1. Actualizar postulación
            $postulacion->estado = 'Rechazado';
            $postulacion->motivos_rechazo = $request->motivos_rechazo;
            $postulacion->observaciones = $request->observaciones;
            $postulacion->validador_id = Auth::id();
            $postulacion->fecha_validacion = Carbon::now();
            $postulacion->save();

            // 2. Si estaba asignado como candidato en hash_votantes, quitar el rol de candidato
            if ($postulacion->id_votante && $postulacion->id_evento) {
                $hash = Hash_votantes::where('id_evento', $postulacion->id_evento)
                    ->where('id_votante', $postulacion->id_votante)
                    ->first();

                if ($hash && $hash->candidato == 1) {
                    $hash->candidato = 0;
                    $hash->save();
                }
            }

            // 3. Registrar en AuditoriaPostulacionCandidato
            AuditoriaPostulacionCandidato::create([
                'postulacion_id' => $postulacion->id,
                'id_votante' => $postulacion->id_votante,
                'id_evento' => $postulacion->id_evento,
                'usuario_id' => Auth::id(),
                'accion' => 'Rechazado',
                'motivos_rechazo' => $request->motivos_rechazo,
                'observaciones' => $request->observaciones,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            DB::commit();

            return redirect()->back()->with('success', "La postulación {$postulacion->radicado} ha sido rechazada y los motivos quedaron registrados en la auditoría.");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error rechazando postulación candidato: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al rechazar la postulación: ' . $e->getMessage());
        }
    }

    /**
     * Muestra el historial completo de auditoría de validaciones de candidatos.
     */
    public function historial(Request $request)
    {
        $this->verificarPermisoValidador('validador-candidatos-historial');

        $accion = $request->input('accion');
        $idEvento = $request->input('id_evento');
        $search = $request->input('search');
        $fechaDesde = $request->input('fecha_desde');
        $fechaHasta = $request->input('fecha_hasta');

        $query = AuditoriaPostulacionCandidato::with([
            'postulacion:id,radicado,nombre,identificacion,tipo_planta,dependencia,provision',
            'evento:id,nombre,estado',
            'usuario:id,name,email',
            'votante:id,nombre,identificacion'
        ]);

        if ($accion) {
            $query->where('accion', $accion);
        }

        if ($idEvento) {
            $query->where('id_evento', $idEvento);
        }

        if ($fechaDesde) {
            $query->whereDate('created_at', '>=', $fechaDesde);
        }

        if ($fechaHasta) {
            $query->whereDate('created_at', '<=', $fechaHasta);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('postulacion', function ($qp) use ($search) {
                    $qp->where('nombre', 'like', "%{$search}%")
                        ->orWhere('identificacion', 'like', "%{$search}%")
                        ->orWhere('radicado', 'like', "%{$search}%");
                })->orWhereHas('usuario', function ($qu) use ($search) {
                    $qu->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            });
        }

        $auditorias = $query->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        $eventos = Eventos::orderBy('id', 'desc')->get(['id', 'nombre', 'estado']);

        $conteoAprobadas = AuditoriaPostulacionCandidato::where('accion', 'Aprobado')->count();
        $conteoRechazadas = AuditoriaPostulacionCandidato::where('accion', 'Rechazado')->count();

        return Inertia::render('InscripcionCandidatos/Historial', [
            'auditorias' => $auditorias,
            'filters' => [
                'accion' => $accion,
                'id_evento' => $idEvento,
                'search' => $search,
                'fecha_desde' => $fechaDesde,
                'fecha_hasta' => $fechaHasta,
            ],
            'metrics' => [
                'total' => $conteoAprobadas + $conteoRechazadas,
                'aprobadas' => $conteoAprobadas,
                'rechazadas' => $conteoRechazadas,
            ],
            'eventos' => $eventos,
        ]);
    }

    /**
     * Valida que el usuario tenga rol 'validador-candidatos', 'Administrador' o el permiso específico.
     */
    private function verificarPermisoValidador($permisoRequerido = 'validador-candidatos-listar')
    {
        $user = Auth::user();
        if (!$user) {
            abort(401, 'No autenticado');
        }

        $tieneAcceso = false;
        if (method_exists($user, 'hasAnyRole')) {
            $tieneAcceso = $user->hasAnyRole(['validador-candidatos', 'Administrador']) || $user->can($permisoRequerido);
        } elseif (isset($user->roles)) {
            $roles = $user->roles->pluck('name')->toArray();
            $tieneAcceso = in_array('validador-candidatos', $roles) || in_array('Administrador', $roles);
        }

        if (!$tieneAcceso) {
            abort(403, 'Acceso denegado: este módulo es exclusivo para usuarios con el rol validador-candidatos o permisos específicos de validación.');
        }
    }
}
