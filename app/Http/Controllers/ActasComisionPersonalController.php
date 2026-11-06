<?php

namespace App\Http\Controllers;

use App\Models\Delegados;
use App\Models\Eventos;
use App\Models\Hash_votantes;
use App\Models\Informacion_votantes;
use App\Models\Postulaciones_candidatos;
use App\Models\RutasVotaciones;
use App\Models\Votos;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class ActasComisionPersonalController extends Controller
{
    protected int $eventoId = 25;

    public function __construct()
    {
        // Se valida el acceso en cada acción o vía middleware
    }

    /**
     * Valida que el usuario tenga rol de validador-candidatos, Supervisor o Administrador.
     */
    private function checkAccess()
    {
        $user = Auth::user();
        if (!$user) {
            abort(403, 'No autenticado');
        }

        $rolesPermitidos = ['validador-candidatos', 'Supervisor', 'Administrador'];
        if ($user->hasAnyRole($rolesPermitidos)) {
            return true;
        }

        if ($user->can('validador-candidatos-listar') || $user->can('actas-comision-listar')) {
            return true;
        }

        abort(403, 'Acceso restringido: Solo para validadores de candidatos y supervisores.');
    }

    /**
     * Resuelve el evento objetivo (ID 25 - Comisión de Personal).
     */
    private function getEvento()
    {
        $evento = Eventos::find($this->eventoId);
        if (!$evento) {
            $evento = Eventos::where('nombre', 'like', '%Comisi%')->first()
                ?? Eventos::where('id', 25)->first()
                ?? Eventos::orderBy('id', 'desc')->first();
        }
        return $evento;
    }

    /**
     * Resuelve las fechas automáticas:
     * - Fechas de inscripción: tomadas del bloqueo de rutas (RutasVotaciones)
     * - Fechas de votación: tomadas del evento oficial
     */
    private function getFechasCronograma($evento)
    {
        // 1. Detección automática en RutasVotaciones para inscripción de candidatos
        $rutaInscripcion = RutasVotaciones::where(function ($q) {
            $q->where('path', 'like', '%inscripcion-candidatos%')
              ->orWhere('path', 'like', '%postulacion-candidato%')
              ->orWhere('path', 'like', '%candidatos%');
        })->orderBy('id', 'desc')->first();

        $fechaInicioInscripcion = null;
        $fechaFinInscripcion = null;
        $rutaPath = null;

        if ($rutaInscripcion) {
            $fechaInicioInscripcion = $rutaInscripcion->fecha_inicio;
            $fechaFinInscripcion = $rutaInscripcion->fecha_fin;
            $rutaPath = $rutaInscripcion->path;
        }

        // Si no está registrado en RutasVotaciones, usar primera postulación o fallback
        if (!$fechaInicioInscripcion) {
            $primeraPostulacion = Postulaciones_candidatos::where('id_evento', $evento?->id ?? $this->eventoId)
                ->orderBy('created_at', 'asc')->first();
            $fechaInicioInscripcion = $primeraPostulacion?->created_at ?? $evento?->fecha_inicio ?? now()->startOfDay()->format('Y-m-d H:i:s');
        }

        if (!$fechaFinInscripcion) {
            $fechaFinInscripcion = $evento?->fecha_fin ?? now()->addDays(5)->endOfDay()->format('Y-m-d H:i:s');
        }

        // 2. Fechas de votaciones tomadas del evento
        $fechaInicioVotacion = $evento?->fecha_inicio ?? now()->format('Y-m-d H:i:s');
        $fechaFinVotacion = $evento?->fecha_fin ?? now()->addDay()->format('Y-m-d H:i:s');

        return [
            'inscripcion_inicio' => $fechaInicioInscripcion,
            'inscripcion_fin' => $fechaFinInscripcion,
            'inscripcion_ruta' => $rutaPath ?? 'inscripcion-candidatos',
            'votacion_inicio' => $fechaInicioVotacion,
            'votacion_fin' => $fechaFinVotacion,
        ];
    }

    /**
     * Obtiene los secretarios firmantes asignados al evento
     */
    private function getFirmantes($eventoId)
    {
        $firmantes = Delegados::where('id_evento', $eventoId)
            ->where('tipo', 'secretario')
            ->where('estado', 1)
            ->get();

        // Si no hay específicos con id_evento = 25, fallback a secretarios activos generales
        if ($firmantes->isEmpty()) {
            $firmantes = Delegados::where('tipo', 'secretario')
                ->where('estado', 1)
                ->get();
        }

        return $firmantes;
    }

    /**
     * Dashboard principal de las 4 Actas de Comisión de Personal y Gestión de Firmantes
     */
    public function index()
    {
        $this->checkAccess();

        $evento = $this->getEvento();
        $eventoId = $evento?->id ?? $this->eventoId;
        $fechas = $this->getFechasCronograma($evento);
        $firmantes = $this->getFirmantes($eventoId);

        $now = Carbon::now();

        // Estados de las actas según el cronograma
        $fechaInscripcionInicio = Carbon::parse($fechas['inscripcion_inicio']);
        $fechaInscripcionFin = Carbon::parse($fechas['inscripcion_fin']);
        $fechaVotacionInicio = Carbon::parse($fechas['votacion_inicio']);
        $fechaVotacionFin = Carbon::parse($fechas['votacion_fin']);

        // Conteo de postulaciones
        $totalPostulaciones = Postulaciones_candidatos::where('id_evento', $eventoId)->count();
        $totalAprobados = Postulaciones_candidatos::where('id_evento', $eventoId)->where('estado', 'aprobado')->count();
        $totalRechazados = Postulaciones_candidatos::where('id_evento', $eventoId)->where('estado', 'rechazado')->count();

        // Conteo de votaciones
        $totalCenso = Hash_votantes::where('id_evento', $eventoId)->count();
        $totalVotos = Votos::where('id_eventos', $eventoId)->count();

        $actas = [
            [
                'id' => 'inscripcion_apertura',
                'codigo' => 'ACT-CP-01',
                'nombre' => 'Acta de Apertura de Inscripción de Candidatos',
                'categoria' => 'Inscripción y Postulaciones',
                'tipo' => 'apertura',
                'descripcion' => 'Constancia legal y técnica de apertura oficial del proceso de recepción de postulaciones.',
                'fecha_oficial' => $fechas['inscripcion_inicio'],
                'fecha_texto' => Carbon::parse($fechas['inscripcion_inicio'])->translatedFormat('d \d\e F \d\e Y - h:i A'),
                'origen_fecha' => 'Bloqueo de Rutas (' . $fechas['inscripcion_ruta'] . ')',
                'estado' => $now->gte($fechaInscripcionInicio) ? 'Disponible' : 'Programada',
                'disponible' => true,
            ],
            [
                'id' => 'inscripcion_cierre',
                'codigo' => 'ACT-CP-02',
                'nombre' => 'Acta de Cierre de Inscripción de Candidatos',
                'categoria' => 'Inscripción y Postulaciones',
                'tipo' => 'cierre',
                'descripcion' => 'Consolidado formal de postulaciones radicadas, aspirantes validados y candidatos habilitados.',
                'fecha_oficial' => $fechas['inscripcion_fin'],
                'fecha_texto' => Carbon::parse($fechas['inscripcion_fin'])->translatedFormat('d \d\e F \d\e Y - h:i A'),
                'origen_fecha' => 'Bloqueo de Rutas (' . $fechas['inscripcion_ruta'] . ')',
                'estado' => $now->gte($fechaInscripcionFin) ? 'Disponible' : ($now->gte($fechaInscripcionInicio) ? 'En Curso' : 'Programada'),
                'disponible' => true,
            ],
            [
                'id' => 'votacion_apertura',
                'codigo' => 'ACT-CP-03',
                'nombre' => 'Acta de Apertura de Votaciones',
                'categoria' => 'Jornada Electoral',
                'tipo' => 'apertura',
                'descripcion' => 'Certificación de puesta a cero de la urna electrónica y apertura formal de la votación virtual.',
                'fecha_oficial' => $fechas['votacion_inicio'],
                'fecha_texto' => Carbon::parse($fechas['votacion_inicio'])->translatedFormat('d \d\e F \d\e Y - h:i A'),
                'origen_fecha' => 'Cronograma Oficial Evento ID ' . $eventoId,
                'estado' => $now->gte($fechaVotacionInicio) ? 'Disponible' : 'Programada',
                'disponible' => true,
            ],
            [
                'id' => 'votacion_cierre',
                'codigo' => 'ACT-CP-04',
                'nombre' => 'Acta de Cierre de Votaciones y Escrutinio',
                'categoria' => 'Jornada Electoral',
                'tipo' => 'cierre',
                'descripcion' => 'Cierre de la jornada electoral, consolidación de sufragios y escrutinio final de resultados.',
                'fecha_oficial' => $fechas['votacion_fin'],
                'fecha_texto' => Carbon::parse($fechas['votacion_fin'])->translatedFormat('d \d\e F \d\e Y - h:i A'),
                'origen_fecha' => 'Cronograma Oficial Evento ID ' . $eventoId,
                'estado' => $now->gte($fechaVotacionFin) ? 'Disponible' : ($now->gte($fechaVotacionInicio) ? 'En Curso' : 'Programada'),
                'disponible' => true,
            ],
        ];

        // Todos los eventos para el selector de asignación de firmantes
        $todosEventos = Eventos::select('id', 'nombre')->get();

        return Inertia::render('ActasComision/Index', [
            'evento' => $evento,
            'fechas' => $fechas,
            'actas' => $actas,
            'firmantes' => $firmantes,
            'estadisticas' => [
                'total_postulaciones' => $totalPostulaciones,
                'total_aprobados' => $totalAprobados,
                'total_rechazados' => $totalRechazados,
                'total_censo' => $totalCenso,
                'total_votos' => $totalVotos,
                'total_firmantes' => $firmantes->count(),
            ],
            'eventos_disponibles' => $todosEventos,
        ]);
    }

    /**
     * Compila y estructura la información detallada de una de las 4 actas
     */
    private function generarDatosActa($tipo)
    {
        $evento = $this->getEvento();
        $eventoId = $evento?->id ?? $this->eventoId;
        $fechas = $this->getFechasCronograma($evento);
        $firmantes = $this->getFirmantes($eventoId);

        // Candidatos y postulaciones
        $postulaciones = Postulaciones_candidatos::where('id_evento', $eventoId)
            ->orderBy('id', 'asc')
            ->get();

        $candidatosAprobados = $postulaciones->where('estado', 'aprobado')->values();
        $postulacionesRechazadas = $postulaciones->where('estado', 'rechazado')->values();
        $postulacionesPendientes = $postulaciones->where('estado', 'pendiente')->values();

        // Conteo por planta
        $aprobadosPlantaCentral = $candidatosAprobados->where('tipo_planta', 'Planta Central')->count();
        $aprobadosSgp = $candidatosAprobados->where('tipo_planta', 'SGP')->count();

        // Estadísticas de votaciones
        $censoVotantes = Hash_votantes::where('id_evento', $eventoId)->count();
        $totalVotos = Votos::where('id_eventos', $eventoId)->count();

        // Votos por candidato
        $resultadosCandidatos = [];
        foreach ($candidatosAprobados as $candidato) {
            $votosCandidato = Votos::where('id_eventos', $eventoId)
                ->where('id_candidato', $candidato->id_votante)
                ->count();

            $porcentaje = $totalVotos > 0 ? round(($votosCandidato / $totalVotos) * 100, 2) : 0;

            $resultadosCandidatos[] = [
                'id' => $candidato->id,
                'id_votante' => $candidato->id_votante,
                'radicado' => $candidato->radicado,
                'nombre' => $candidato->nombre,
                'identificacion' => $candidato->identificacion,
                'tipo_planta' => $candidato->tipo_planta,
                'dependencia' => $candidato->dependencia,
                'votos' => $votosCandidato,
                'porcentaje' => $porcentaje,
            ];
        }

        // Definiciones de cada tipo de acta
        $configActas = [
            'inscripcion_apertura' => [
                'codigo' => 'ACT-CP-01',
                'titulo' => 'ACTA DE APERTURA DEL PROCESO DE INSCRIPCIÓN Y POSTULACIÓN DE CANDIDATOS',
                'subtitulo' => 'ELECCIÓN DE REPRESENTANTES DE LOS EMPLEADOS ANTE LA COMISIÓN DE PERSONAL',
                'fecha_acta' => $fechas['inscripcion_inicio'],
                'fecha_cierre_prevista' => $fechas['inscripcion_fin'],
                'origen_fecha' => 'Bloqueo de Rutas (' . $fechas['inscripcion_ruta'] . ')',
                'objeto' => 'Dejar constancia formal y técnica de la apertura del proceso oficial de inscripción y postulación de funcionarios para conformar la Comisión de Personal de la Alcaldía de Pereira, a través de la plataforma virtual VOTA YA.',
            ],
            'inscripcion_cierre' => [
                'codigo' => 'ACT-CP-02',
                'titulo' => 'ACTA DE CIERRE DEL PROCESO DE INSCRIPCIÓN Y POSTULACIÓN DE CANDIDATOS',
                'subtitulo' => 'ELECCIÓN DE REPRESENTANTES DE LOS EMPLEADOS ANTE LA COMISIÓN DE PERSONAL',
                'fecha_acta' => $fechas['inscripcion_fin'],
                'fecha_apertura' => $fechas['inscripcion_inicio'],
                'origen_fecha' => 'Bloqueo de Rutas (' . $fechas['inscripcion_ruta'] . ')',
                'objeto' => 'Certificar la culminación y cierre del período de recepción de postulaciones, consolidando el número de aspirantes radicados, resultados del proceso de validación documental y la lista definitiva de candidatos habilitados para participar en la contienda electoral.',
            ],
            'votacion_apertura' => [
                'codigo' => 'ACT-CP-03',
                'titulo' => 'ACTA DE APERTURA DE LA JORNADA ELECTORAL Y PUESTA A CERO',
                'subtitulo' => 'ELECCIÓN DE REPRESENTANTES DE LOS EMPLEADOS ANTE LA COMISIÓN DE PERSONAL',
                'fecha_acta' => $fechas['votacion_inicio'],
                'fecha_cierre_prevista' => $fechas['votacion_fin'],
                'origen_fecha' => 'Cronograma Oficial Evento ID ' . $eventoId,
                'objeto' => 'Dejar constancia formal de la apertura de los canales electrónicos de sufragio en la plataforma VOTA YA, verificando y certificando la puesta a cero (0 votos registrados) de la urna virtual y la habilitación del censo electoral oficial.',
            ],
            'votacion_cierre' => [
                'codigo' => 'ACT-CP-04',
                'titulo' => 'ACTA DE CIERRE DE LA JORNADA ELECTORAL Y ESCRUTINIO FINAL',
                'subtitulo' => 'ELECCIÓN DE REPRESENTANTES DE LOS EMPLEADOS ANTE LA COMISIÓN DE PERSONAL',
                'fecha_acta' => $fechas['votacion_fin'],
                'fecha_apertura' => $fechas['votacion_inicio'],
                'origen_fecha' => 'Cronograma Oficial Evento ID ' . $eventoId,
                'objeto' => 'Certificar el cierre formal de la votación virtual, la inviolabilidad del sistema de sufragio electrónico, la consolidación del total de votos emitidos y los resultados del escrutinio final para la elección de los representantes ante la Comisión de Personal.',
            ],
        ];

        if (!isset($configActas[$tipo])) {
            abort(404, 'Tipo de acta no válido.');
        }

        $config = $configActas[$tipo];

        return [
            'tipo' => $tipo,
            'config' => $config,
            'evento' => $evento,
            'fechas' => $fechas,
            'firmantes' => $firmantes,
            'postulaciones' => $postulaciones,
            'candidatos_aprobados' => $candidatosAprobados,
            'postulaciones_rechazadas' => $postulacionesRechazadas,
            'postulaciones_pendientes' => $postulacionesPendientes,
            'totales' => [
                'total_postulaciones' => $postulaciones->count(),
                'total_aprobados' => $candidatosAprobados->count(),
                'total_rechazados' => $postulacionesRechazadas->count(),
                'total_pendientes' => $postulacionesPendientes->count(),
                'aprobados_planta_central' => $aprobadosPlantaCentral,
                'aprobados_sgp' => $aprobadosSgp,
                'censo_votantes' => $censoVotantes,
                'total_votos' => $totalVotos,
                'porcentaje_participacion' => $censoVotantes > 0 ? round(($totalVotos / $censoVotantes) * 100, 2) : 0,
            ],
            'resultados_candidatos' => $resultadosCandidatos,
            'fecha_generacion' => Carbon::now()->translatedFormat('d \d\e F \d\e Y - h:i A'),
        ];
    }

    /**
     * Muestra la vista web interactiva del Acta oficial
     */
    public function show($tipo)
    {
        $this->checkAccess();

        $data = $this->generarDatosActa($tipo);

        return Inertia::render('ActasComision/Show', $data);
    }

    /**
     * Exporta el acta en formato PDF oficial institucional de la Alcaldía de Pereira
     */
    public function pdf($tipo)
    {
        $this->checkAccess();

        $data = $this->generarDatosActa($tipo);

        // Convertir logos e imágenes a base64 para evitar errores de renderizado en DomPDF
        $logoPath = public_path('assets/img/logo.png');
        $data['logo_base64'] = $this->fileToBase64($logoPath);

        $votaYaLogoPath = public_path('assets/img/votaYa.png');
        $data['votaya_logo_base64'] = $this->fileToBase64($votaYaLogoPath);

        // Convertir firmas de secretarios a base64
        $firmantesData = [];
        foreach ($data['firmantes'] as $firmante) {
            $firmaPath = storage_path('app/public/uploads/delegado/' . $firmante->firma);
            $firmantesData[] = [
                'id' => $firmante->id,
                'nombre' => $firmante->nombre,
                'cargo' => $firmante->cargo,
                'identificacion' => $firmante->identificacion,
                'firma_base64' => $this->fileToBase64($firmaPath),
            ];
        }
        $data['firmantes_pdf'] = $firmantesData;

        $nombreArchivo = 'Acta_' . strtoupper($tipo) . '_Comision_Personal_' . date('Ymd_His') . '.pdf';

        $pdf = Pdf::loadView('pdf.acta_comision', $data)
            ->setPaper('letter', 'portrait')
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'defaultFont' => 'Montserrat, sans-serif',
            ]);

        return $pdf->download($nombreArchivo);
    }

    /**
     * Helper para codificar archivos a data URI Base64
     */
    private function fileToBase64($path)
    {
        if (file_exists($path)) {
            $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
            $mime = 'image/png';
            if (in_array($extension, ['jpg', 'jpeg'])) {
                $mime = 'image/jpeg';
            } elseif ($extension === 'webp') {
                $mime = 'image/webp';
            } elseif ($extension === 'svg') {
                $mime = 'image/svg+xml';
            }
            $content = file_get_contents($path);
            return 'data:' . $mime . ';base64,' . base64_encode($content);
        }
        return null;
    }

    /**
     * Lista los secretarios firmantes del evento
     */
    public function firmantes()
    {
        $this->checkAccess();

        $evento = $this->getEvento();
        $eventoId = $evento?->id ?? $this->eventoId;

        $firmantes = Delegados::where('id_evento', $eventoId)
            ->where('tipo', 'secretario')
            ->orderBy('id', 'desc')
            ->get();

        return response()->json([
            'firmantes' => $firmantes,
            'evento' => $evento,
        ]);
    }

    /**
     * Registra un nuevo secretario firmante para el evento
     */
    public function guardarFirmante(Request $request)
    {
        $this->checkAccess();

        $request->validate([
            'nombre' => 'required|string|max:200',
            'cargo' => 'required|string|max:150',
            'identificacion' => 'required|string|max:50',
            'firma' => 'required',
            'id_evento' => 'nullable|integer',
        ], [
            'nombre.required' => 'El nombre del firmante es obligatorio.',
            'cargo.required' => 'El cargo institucional es obligatorio.',
            'identificacion.required' => 'El número de identificación es obligatorio.',
            'firma.required' => 'La firma digital es obligatoria (dibujada o imagen).',
        ]);

        $eventoId = $request->id_evento ?? $this->eventoId;
        $folder = 'delegado';
        $destinationPath = storage_path('app/public/uploads/' . $folder);

        if (!file_exists($destinationPath)) {
            mkdir($destinationPath, 0755, true);
        }

        $fileName = 'NA';

        if ($request->hasFile('firma')) {
            $file = $request->file('firma');
            $extension = strtolower($file->getClientOriginalExtension());
            $fileName = time() . '_secretario_' . preg_replace('/[^A-Za-z0-9]/', '', $request->identificacion) . '.' . $extension;
            $file->move($destinationPath, $fileName);
        } elseif (is_string($request->firma) && str_starts_with($request->firma, 'data:image')) {
            // Manejar firma dibujada en canvas (base64)
            $imageParts = explode(";base64,", $request->firma);
            $imageBase64 = base64_decode($imageParts[1]);
            $fileName = time() . '_secretario_' . preg_replace('/[^A-Za-z0-9]/', '', $request->identificacion) . '.png';
            file_put_contents($destinationPath . '/' . $fileName, $imageBase64);
        }

        $delegado = Delegados::create([
            'id_evento' => $eventoId,
            'nombre' => $request->nombre,
            'identificacion' => $request->identificacion,
            'cargo' => $request->cargo,
            'tipo' => 'secretario',
            'firma' => $fileName,
            'estado' => 1,
        ]);

        return redirect()->back()->with('success', 'Secretario firmante registrado exitosamente.');
    }

    /**
     * Actualiza datos o firma de un secretario firmante
     */
    public function actualizarFirmante(Request $request, $id)
    {
        $this->checkAccess();

        $request->validate([
            'nombre' => 'required|string|max:200',
            'cargo' => 'required|string|max:150',
            'identificacion' => 'required|string|max:50',
            'id_evento' => 'nullable|integer',
        ]);

        $firmante = Delegados::findOrFail($id);

        $folder = 'delegado';
        $destinationPath = storage_path('app/public/uploads/' . $folder);

        if ($request->hasFile('firma')) {
            $file = $request->file('firma');
            $extension = strtolower($file->getClientOriginalExtension());
            $fileName = time() . '_secretario_' . preg_replace('/[^A-Za-z0-9]/', '', $request->identificacion) . '.' . $extension;
            $file->move($destinationPath, $fileName);
            $firmante->firma = $fileName;
        } elseif (is_string($request->firma) && str_starts_with($request->firma, 'data:image')) {
            $imageParts = explode(";base64,", $request->firma);
            $imageBase64 = base64_decode($imageParts[1]);
            $fileName = time() . '_secretario_' . preg_replace('/[^A-Za-z0-9]/', '', $request->identificacion) . '.png';
            file_put_contents($destinationPath . '/' . $fileName, $imageBase64);
            $firmante->firma = $fileName;
        }

        $firmante->nombre = $request->nombre;
        $firmante->cargo = $request->cargo;
        $firmante->identificacion = $request->identificacion;
        if ($request->has('id_evento')) {
            $firmante->id_evento = $request->id_evento;
        }
        if ($request->has('estado')) {
            $firmante->estado = $request->estado;
        }
        $firmante->save();

        return redirect()->back()->with('success', 'Secretario firmante actualizado con éxito.');
    }

    /**
     * Elimina un firmante
     */
    public function eliminarFirmante($id)
    {
        $this->checkAccess();

        $firmante = Delegados::findOrFail($id);
        $firmante->delete();

        return redirect()->back()->with('success', 'Secretario firmante eliminado correctamente.');
    }
}

