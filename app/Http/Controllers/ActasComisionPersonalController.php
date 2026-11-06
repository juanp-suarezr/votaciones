<?php

namespace App\Http\Controllers;

use App\Models\Delegados;
use App\Models\Eventos;
use App\Models\Hash_proyectos;
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
    protected int $eventoDefaultId = 25;

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

        abort(403, 'Acceso restringido: Solo para validadores de candidatos, supervisores y administradores.');
    }

    /**
     * Resuelve el evento solicitado o el predeterminado (ID 25).
     */
    private function getEvento($eventoId = null)
    {
        if ($eventoId) {
            $ev = Eventos::find($eventoId);
            if ($ev) {
                return $ev;
            }
        }

        return Eventos::find($this->eventoDefaultId)
            ?? Eventos::where('nombre', 'like', '%Comisi%')->first()
            ?? Eventos::orderBy('id', 'desc')->first();
    }

    /**
     * Determina si el evento tiene proceso de inscripción/postulación de candidatos.
     */
    private function esEventoConInscripcion($evento)
    {
        if (!$evento) {
            return false;
        }
        if ($evento->id == 25) {
            return true;
        }
        if (str_contains(strtolower($evento->nombre), 'comisi')) {
            return true;
        }

        return Postulaciones_candidatos::where('id_evento', $evento->id)->exists();
    }

    /**
     * Resuelve las fechas automáticas:
     * - Fechas de inscripción: tomadas del bloqueo de rutas (RutasVotaciones)
     * - Fechas de votación: tomadas del evento oficial
     */
    private function getFechasCronograma($evento)
    {
        $eventoId = $evento?->id ?? $this->eventoDefaultId;

        // 1. Detección automática en RutasVotaciones para inscripción de candidatos
        $rutaInscripcion = RutasVotaciones::where(function ($q) {
            $q->where('path', 'like', '%inscripcion-candidatos%')
              ->orWhere('path', 'like', '%postulacion-candidato%')
              ->orWhere('path', 'like', '%candidatos%');
        })->orderBy('id', 'desc')->first();

        $fechaInicioInscripcion = $rutaInscripcion?->fecha_inicio;
        $fechaFinInscripcion = $rutaInscripcion?->fecha_fin;
        $rutaPath = $rutaInscripcion?->path;

        if (!$fechaInicioInscripcion) {
            $primeraPostulacion = Postulaciones_candidatos::where('id_evento', $eventoId)
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
     * Obtiene los secretarios firmantes:
     * 1. Obligatorio en TODAS las actas: Secretario de las TIC (tabla delegados cargo = 'Secretario de las TIC').
     * 2. Si el evento tiene secretarios específicos, se incluyen con sus firmas.
     * 3. Si NO tiene secretarios específicos, se deja el espacio oficial con el nombre de la dependencia del evento.
     */
    private function getFirmantes($evento)
    {
        $eventoId = $evento?->id ?? $this->eventoDefaultId;

        // 1. Buscar Secretario de las TIC en la tabla delegados
        $secretarioTic = Delegados::where(function ($q) {
            $q->where('cargo', 'like', '%Secretario de las TIC%')
              ->orWhere('cargo', 'like', '%Secretaría de las TIC%')
              ->orWhere('cargo', 'like', '%Secretario TIC%')
              ->orWhere('cargo', 'like', '%TIC%');
        })->where('estado', 1)->first();

        if (!$secretarioTic) {
            $secretarioTic = Delegados::where('cargo', 'like', '%TIC%')->first();
        }

        // 2. Secretarios asignados específicamente a este evento
        $secretariosEvento = Delegados::where('id_evento', $eventoId)
            ->where('tipo', 'secretario')
            ->where('estado', 1)
            ->when($secretarioTic, function ($q) use ($secretarioTic) {
                $q->where('id', '!=', $secretarioTic->id);
            })
            ->get();

        $firmantes = collect();

        // Agregar obligatoriamente al Secretario de las TIC
        if ($secretarioTic) {
            $firmantes->push($secretarioTic);
        } else {
            // Representación institucional en caso de no estar registrado aún en BD
            $firmantes->push((object) [
                'id' => -1,
                'nombre' => 'SECRETARIO DE LAS TIC',
                'cargo' => 'Secretario de las TIC',
                'identificacion' => 'Alcaldía de Pereira',
                'firma' => null,
                'is_tic' => true,
            ]);
        }

        // 3. Si el evento tiene secretarios específicos asignados
        if ($secretariosEvento->isNotEmpty()) {
            foreach ($secretariosEvento as $sec) {
                $firmantes->push($sec);
            }
        } else {
            // "si no tiene firmas de secretario en especifico dejar el nombre de la dependencia del evento"
            $nombreDependencia = ($evento && !empty($evento->dependencias) && $evento->dependencias !== 'NA')
                ? $evento->dependencias
                : 'Secretaría / Dependencia Responsable del Evento';

            $firmantes->push((object) [
                'id' => 0,
                'nombre' => 'SECRETARIO(A) / REPRESENTANTE DELEGADO(A)',
                'cargo' => $nombreDependencia,
                'identificacion' => 'Alcaldía de Pereira',
                'firma' => null,
                'is_dependencia' => true,
            ]);
        }

        return $firmantes;
    }

    /**
     * Obtiene las opciones de votación (candidatos o proyectos) y sus votos
     */
    private function getOpcionesElectorales($eventoId)
    {
        $totalVotos = Votos::where('id_eventos', $eventoId)->count();

        // 1. Postulaciones aprobadas (Comisión de Personal u otros con postulaciones)
        $postulacionesAprobadas = Postulaciones_candidatos::where('id_evento', $eventoId)
            ->where('estado', 'aprobado')
            ->get();

        if ($postulacionesAprobadas->isNotEmpty()) {
            return $postulacionesAprobadas->map(function ($c) use ($eventoId, $totalVotos) {
                $votos = Votos::where('id_eventos', $eventoId)
                    ->where('id_candidato', $c->id_votante)
                    ->count();
                return [
                    'id' => $c->id,
                    'nombre' => $c->nombre,
                    'identificacion' => $c->identificacion,
                    'subtitulo' => $c->tipo_planta ?? 'Candidato',
                    'dependencia' => $c->dependencia ?? 'N/A',
                    'votos' => $votos,
                    'porcentaje' => $totalVotos > 0 ? round(($votos / $totalVotos) * 100, 2) : 0,
                    'tipo_opcion' => 'candidato',
                ];
            })->sortByDesc('votos')->values();
        }

        // 2. Candidatos en Hash_votantes (cuando se crean candidatos directamente para el evento)
        $candidatosHash = Hash_votantes::where('id_evento', $eventoId)
            ->where('candidato', 1)
            ->with('votante')
            ->get();

        if ($candidatosHash->isNotEmpty()) {
            return $candidatosHash->map(function ($h) use ($eventoId, $totalVotos) {
                $nombre = $h->votante ? ($h->votante->nombre . ' ' . $h->votante->apellido) : ('Candidato #' . $h->id);
                $identificacion = $h->votante ? $h->votante->identificacion : 'N/A';
                $votos = Votos::where('id_eventos', $eventoId)
                    ->where('id_candidato', $h->id_votante)
                    ->count();
                return [
                    'id' => $h->id,
                    'nombre' => $nombre,
                    'identificacion' => $identificacion,
                    'subtitulo' => $h->tipo ?? 'Candidato',
                    'dependencia' => 'Candidato Habilitado',
                    'votos' => $votos,
                    'porcentaje' => $totalVotos > 0 ? round(($votos / $totalVotos) * 100, 2) : 0,
                    'tipo_opcion' => 'candidato',
                ];
            })->sortByDesc('votos')->values();
        }

        // 3. Proyectos (Presupuesto Participativo u otros con proyectos)
        $proyectos = Hash_proyectos::where('id_evento', $eventoId)
            ->with('proyecto')
            ->get();

        if ($proyectos->isNotEmpty()) {
            return $proyectos->map(function ($hp) use ($eventoId, $totalVotos) {
                $nombre = $hp->proyecto ? $hp->proyecto->nombre : ('Proyecto #' . $hp->id_proyecto);
                $votos = Votos::where('id_eventos', $eventoId)
                    ->where('id_proyecto', $hp->id_proyecto)
                    ->count();
                return [
                    'id' => $hp->id,
                    'nombre' => $nombre,
                    'identificacion' => 'PROY-' . $hp->id_proyecto,
                    'subtitulo' => $hp->comuna ?? 'Presupuesto Participativo',
                    'dependencia' => $hp->proyecto ? $hp->proyecto->descripcion : 'N/A',
                    'votos' => $votos,
                    'porcentaje' => $totalVotos > 0 ? round(($votos / $totalVotos) * 100, 2) : 0,
                    'tipo_opcion' => 'proyecto',
                ];
            })->sortByDesc('votos')->values();
        }

        return collect();
    }

    /**
     * Dashboard principal de Actas Oficiales y Gestión de Firmantes para cualquier evento
     */
    public function index(Request $request)
    {
        $this->checkAccess();

        $eventoId = $request->query('id_evento');
        $evento = $this->getEvento($eventoId);
        $eventoId = $evento?->id ?? $this->eventoDefaultId;

        $esComision = $this->esEventoConInscripcion($evento);
        $fechas = $this->getFechasCronograma($evento);
        $firmantes = $this->getFirmantes($evento);

        $now = Carbon::now();
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

        $actas = [];

        // Si el evento tiene inscripciones de candidatos (como Comisión de Personal), incluir las 2 actas de inscripción
        if ($esComision) {
            $actas[] = [
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
            ];

            $actas[] = [
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
            ];
        }

        // Para TODOS los eventos: Actas de inicio y cierre de votaciones
        $actas[] = [
            'id' => 'votacion_apertura',
            'codigo' => $esComision ? 'ACT-CP-03' : 'ACT-EV-01',
            'nombre' => 'Acta de Apertura de la Jornada de Votación',
            'categoria' => 'Jornada Electoral',
            'tipo' => 'apertura',
            'descripcion' => 'Certificación de puesta a cero de la urna electrónica y apertura formal de la votación virtual.',
            'fecha_oficial' => $fechas['votacion_inicio'],
            'fecha_texto' => Carbon::parse($fechas['votacion_inicio'])->translatedFormat('d \d\e F \d\e Y - h:i A'),
            'origen_fecha' => 'Cronograma Oficial Evento ID ' . $eventoId,
            'estado' => $now->gte($fechaVotacionInicio) ? 'Disponible' : 'Programada',
            'disponible' => true,
        ];

        $actas[] = [
            'id' => 'votacion_cierre',
            'codigo' => $esComision ? 'ACT-CP-04' : 'ACT-EV-02',
            'nombre' => 'Acta de Cierre de Votaciones y Escrutinio',
            'categoria' => 'Jornada Electoral',
            'tipo' => 'cierre',
            'descripcion' => 'Cierre de la jornada electoral, consolidación de sufragios y escrutinio final de resultados.',
            'fecha_oficial' => $fechas['votacion_fin'],
            'fecha_texto' => Carbon::parse($fechas['votacion_fin'])->translatedFormat('d \d\e F \d\e Y - h:i A'),
            'origen_fecha' => 'Cronograma Oficial Evento ID ' . $eventoId,
            'estado' => $now->gte($fechaVotacionFin) ? 'Disponible' : ($now->gte($fechaVotacionInicio) ? 'En Curso' : 'Programada'),
            'disponible' => true,
        ];

        // Listado de todos los eventos para el selector dinámico
        $todosEventos = Eventos::select('id', 'nombre', 'dependencias', 'fecha_inicio', 'fecha_fin', 'estado')
            ->orderBy('id', 'desc')
            ->get();

        // Buscar secretario TIC específico para destacarlo en la vista
        $secretarioTic = Delegados::where(function ($q) {
            $q->where('cargo', 'like', '%Secretario de las TIC%')
              ->orWhere('cargo', 'like', '%Secretaría de las TIC%')
              ->orWhere('cargo', 'like', '%TIC%');
        })->where('estado', 1)->first();

        return Inertia::render('ActasComision/Index', [
            'evento' => $evento,
            'es_comision' => $esComision,
            'fechas' => $fechas,
            'actas' => $actas,
            'firmantes' => $firmantes,
            'secretario_tic' => $secretarioTic,
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
     * Compila y estructura la información detallada de un acta para el evento dado
     */
    private function generarDatosActa($tipo, $eventoId = null)
    {
        $evento = $this->getEvento($eventoId);
        $eventoId = $evento?->id ?? $this->eventoDefaultId;
        $esComision = $this->esEventoConInscripcion($evento);

        $fechas = $this->getFechasCronograma($evento);
        $firmantes = $this->getFirmantes($evento);

        // Postulaciones (si aplica)
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

        // Opciones electorales (candidatos o proyectos según el evento)
        $opcionesElectorales = $this->getOpcionesElectorales($eventoId);

        // Nombre de la dependencia oficial del evento
        $dependenciaEvento = ($evento && !empty($evento->dependencias) && $evento->dependencias !== 'NA')
            ? $evento->dependencias
            : 'Secretaría / Dependencia Responsable';

        // Definiciones de cada tipo de acta
        $configActas = [
            'inscripcion_apertura' => [
                'codigo' => 'ACT-CP-01',
                'titulo' => 'ACTA DE APERTURA DEL PROCESO DE INSCRIPCIÓN Y POSTULACIÓN DE CANDIDATOS',
                'subtitulo' => 'PROCESO DE POSTULACIÓN - ' . strtoupper($evento->nombre ?? 'COMISIÓN DE PERSONAL'),
                'fecha_acta' => $fechas['inscripcion_inicio'],
                'fecha_cierre_prevista' => $fechas['inscripcion_fin'],
                'origen_fecha' => 'Bloqueo de Rutas (' . $fechas['inscripcion_ruta'] . ')',
                'objeto' => 'Dejar constancia formal y técnica de la apertura del proceso oficial de recepción de postulaciones e inscripciones para ' . ($evento->nombre ?? 'el evento electoral') . ' de la Alcaldía de Pereira, a través de la plataforma virtual VOTA YA.',
            ],
            'inscripcion_cierre' => [
                'codigo' => 'ACT-CP-02',
                'titulo' => 'ACTA DE CIERRE DEL PROCESO DE INSCRIPCIÓN Y POSTULACIÓN DE CANDIDATOS',
                'subtitulo' => 'PROCESO DE POSTULACIÓN - ' . strtoupper($evento->nombre ?? 'COMISIÓN DE PERSONAL'),
                'fecha_acta' => $fechas['inscripcion_fin'],
                'fecha_apertura' => $fechas['inscripcion_inicio'],
                'origen_fecha' => 'Bloqueo de Rutas (' . $fechas['inscripcion_ruta'] . ')',
                'objeto' => 'Certificar la culminación y cierre del período de recepción de postulaciones, consolidando el número de aspirantes radicados, resultados de validación documental y la lista definitiva de candidatos habilitados.',
            ],
            'votacion_apertura' => [
                'codigo' => $esComision ? 'ACT-CP-03' : 'ACT-EV-01',
                'titulo' => 'ACTA DE APERTURA DE LA JORNADA ELECTORAL Y PUESTA A CERO',
                'subtitulo' => 'JORNADA OFICIAL DE VOTACIÓN - ' . strtoupper($evento->nombre ?? 'ELECCIONES INSTITUCIONALES'),
                'fecha_acta' => $fechas['votacion_inicio'],
                'fecha_cierre_prevista' => $fechas['votacion_fin'],
                'origen_fecha' => 'Cronograma Oficial Evento ID ' . $eventoId,
                'objeto' => 'Dejar constancia formal de la apertura de los canales electrónicos de sufragio en la plataforma VOTA YA para el evento "' . ($evento->nombre ?? 'Votación Institucional') . '", verificando y certificando la puesta a cero (0 votos registrados) de la urna virtual y la habilitación del censo electoral oficial.',
            ],
            'votacion_cierre' => [
                'codigo' => $esComision ? 'ACT-CP-04' : 'ACT-EV-02',
                'titulo' => 'ACTA DE CIERRE DE LA JORNADA ELECTORAL Y ESCRUTINIO FINAL',
                'subtitulo' => 'JORNADA OFICIAL DE VOTACIÓN - ' . strtoupper($evento->nombre ?? 'ELECCIONES INSTITUCIONALES'),
                'fecha_acta' => $fechas['votacion_fin'],
                'fecha_apertura' => $fechas['votacion_inicio'],
                'origen_fecha' => 'Cronograma Oficial Evento ID ' . $eventoId,
                'objeto' => 'Certificar el cierre formal de la votación virtual para "' . ($evento->nombre ?? 'Votación Institucional') . '", la inviolabilidad del sistema de sufragio electrónico, la consolidación del total de votos emitidos y los resultados del escrutinio final.',
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
            'dependencia_evento' => $dependenciaEvento,
            'es_comision' => $esComision,
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
            'resultados_candidatos' => $opcionesElectorales,
            'fecha_generacion' => Carbon::now()->translatedFormat('d \d\e F \d\e Y - h:i A'),
        ];
    }

    /**
     * Muestra la vista web interactiva del Acta oficial
     */
    public function show(Request $request, $tipo)
    {
        $this->checkAccess();

        $eventoId = $request->query('id_evento');
        $data = $this->generarDatosActa($tipo, $eventoId);

        return Inertia::render('ActasComision/Show', $data);
    }

    /**
     * Exporta el acta en formato PDF oficial institucional de la Alcaldía de Pereira
     */
    public function pdf(Request $request, $tipo)
    {
        $this->checkAccess();

        $eventoId = $request->query('id_evento');
        $data = $this->generarDatosActa($tipo, $eventoId);

        // Convertir logos e imágenes a base64 para evitar errores de renderizado en DomPDF
        $logoPath = public_path('assets/img/logo.png');
        $data['logo_base64'] = $this->fileToBase64($logoPath);

        $votaYaLogoPath = public_path('assets/img/votaYa.png');
        $data['votaya_logo_base64'] = $this->fileToBase64($votaYaLogoPath);

        // Convertir firmas de secretarios a base64
        $firmantesData = [];
        foreach ($data['firmantes'] as $firmante) {
            $firmaBase64 = null;
            if (!empty($firmante->firma)) {
                $firmaPath = storage_path('app/public/uploads/delegado/' . $firmante->firma);
                $firmaBase64 = $this->fileToBase64($firmaPath);
            }

            $firmantesData[] = [
                'id' => $firmante->id ?? 0,
                'nombre' => $firmante->nombre,
                'cargo' => $firmante->cargo,
                'identificacion' => $firmante->identificacion,
                'is_dependencia' => !empty($firmante->is_dependencia),
                'is_tic' => !empty($firmante->is_tic),
                'firma_base64' => $firmaBase64,
            ];
        }
        $data['firmantes_pdf'] = $firmantesData;

        $eventoSlug = preg_replace('/[^A-Za-z0-9]/', '_', $data['evento']->nombre ?? 'Evento');
        $nombreArchivo = 'Acta_' . strtoupper($tipo) . '_' . $eventoSlug . '_' . date('Ymd_His') . '.pdf';

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
    public function firmantes(Request $request)
    {
        $this->checkAccess();

        $eventoId = $request->query('id_evento');
        $evento = $this->getEvento($eventoId);
        $eventoId = $evento?->id ?? $this->eventoDefaultId;

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

        $eventoId = $request->id_evento ?? $this->eventoDefaultId;
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
