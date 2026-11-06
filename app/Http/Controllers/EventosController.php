<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Acta_escrutino;
use App\Models\Acta_fin;
use App\Models\Acta_inicio;
use App\Models\Eventos;
use App\Models\Hash_eventos_hijos;
use App\Models\Hash_proyectos;
use App\Models\Hash_votantes;
use App\Models\ParametrosDetalle;
use App\Models\Puntos;
use App\Models\Tipos;
use App\Models\Votos;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;

class EventosController extends Controller
{

    function __construct()
    {
        $this->middleware('permission:eventos-listar|eventos-crear|eventos-editar|eventos-eliminar', ['only' => ['index', 'show', 'store', 'migrarUsuariosView', 'estadisticasVotantesEvento', 'ejecutarMigracionUsuarios', 'listarVotantesEvento']]);
        $this->middleware('permission:eventos-crear', ['only' => ['create', 'store']]);
        $this->middleware('permission:eventos-editar', ['only' => ['edit', 'update', 'migrarUsuariosView', 'estadisticasVotantesEvento', 'ejecutarMigracionUsuarios']]);
        $this->middleware('permission:eventos-eliminar', ['only' => ['destroy']]);
    }

    public function index(Request $request)
    {
        $search = $request->input('search');
        $estado = $request->input('estado');

        $eventos = Eventos::whereNot('nombre', 'Admin')
            ->when($search, function ($q, $search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('nombre', 'like', '%' . $search . '%')
                        ->orWhere('dependencias', 'like', '%' . $search . '%')
                        ->orWhere('descripcion', 'like', '%' . $search . '%');
                });
            })
            ->when($estado, function ($q, $estado) {
                $q->where('estado', $estado);
            })
            ->withCount(['votantes', 'votos', 'acta_escrutinio'])
            ->orderBy('id', 'desc')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Eventos/Index', [
            'eventos' => $eventos,
            'filters' => [
                'search' => $search,
                'estado' => $estado,
            ],
        ]);
    }

    public function show($id)
    {
        $evento = Eventos::whereNot('nombre', 'Admin')
            ->with(['padre'])
            ->withCount(['votantes', 'votos', 'acta_escrutinio'])
            ->findOrFail($id);

        // Desglose de votantes asignados
        $conteoVotantes = [
            'total' => Hash_votantes::where('id_evento', $id)->count(),
            'activos' => Hash_votantes::where('id_evento', $id)->where('estado', 'Activo')->count(),
            'pendientes' => Hash_votantes::where('id_evento', $id)->where('estado', 'Pendiente')->count(),
            'rechazados' => Hash_votantes::where('id_evento', $id)->where('estado', 'Rechazado')->count(),
            'candidatos' => Hash_votantes::where('id_evento', $id)->where('candidato', 1)->count(),
            'solo_votantes' => Hash_votantes::where('id_evento', $id)->where('candidato', 0)->count(),
        ];

        // Desglose de votos
        $conteoVotos = [
            'total' => Votos::where('id_eventos', $id)->count(),
            'virtuales' => Votos::where('id_eventos', $id)->where('isVirtual', 1)->count(),
            'presenciales' => Votos::where('id_eventos', $id)->where('isVirtual', 0)->count(),
        ];

        // Actas de inicio y fin
        $actasInicio = Acta_inicio::where('id_evento', $id)
            ->with('jurado')
            ->orderBy('id', 'desc')
            ->get();

        $actasFin = Acta_fin::where('id_evento', $id)
            ->with('jurado')
            ->orderBy('id', 'desc')
            ->get();

        // Actas de escrutinio
        $actasEscrutinio = Acta_escrutino::where('id_evento', $id)
            ->with(['jurado', 'cordinador'])
            ->orderBy('id', 'desc')
            ->get();

        // Eventos hijos si este evento es padre
        $eventosHijos = Hash_eventos_hijos::where('id_evento_padre', $id)
            ->join('eventos', 'hash_eventos_hijos.id_evento_hijo', '=', 'eventos.id')
            ->select('eventos.id', 'eventos.nombre', 'eventos.estado', 'eventos.fecha_inicio', 'eventos.fecha_fin')
            ->get();

        // Primera página de usuarios asignados para mostrar inmediatamente en la vista (candidatos de primero)
        $votantesPaginados = Hash_votantes::where('id_evento', $id)
            ->with(['evento:id,nombre', 'votante:id,nombre,email,identificacion,tipo_documento,comuna,celular'])
            ->orderByRaw('CASE WHEN hash_votantes.candidato = 1 THEN 0 ELSE 1 END ASC')
            ->orderBy('hash_votantes.id', 'desc')
            ->paginate(10);

        $votantesPaginados = $this->enriquecerVotantes($votantesPaginados, $id, $evento->nombre);

        return Inertia::render('Eventos/Show', [
            'evento' => $evento,
            'conteo_votantes' => $conteoVotantes,
            'conteo_votos' => $conteoVotos,
            'actas_inicio' => $actasInicio,
            'actas_fin' => $actasFin,
            'actas_escrutinio' => $actasEscrutinio,
            'eventos_hijos' => $eventosHijos,
            'votantes_iniciales' => $votantesPaginados,
        ]);
    }

    /**
     * Enriquecer la colección de votantes con estado de sufragio, comuna y eventos de candidatura.
     */
    private function enriquecerVotantes($paginador, $idEvento, $nombreEventoActual = null)
    {
        $votanteIds = $paginador->getCollection()->pluck('id_votante')->filter()->unique()->toArray();

        // Votantes que ya sufragaron en este evento
        $votaron = Votos::where('id_eventos', $idEvento)
            ->whereIn('id_votante', $votanteIds)
            ->pluck('id_votante')
            ->flip()
            ->toArray();

        // Nombres de comunas
        $comunaIds = $paginador->getCollection()->pluck('votante.comuna')->filter()->unique()->toArray();
        $nombresComunas = ParametrosDetalle::whereIn('id', $comunaIds)->pluck('detalle', 'id')->toArray();

        // Eventos específicos donde cada votante es candidato
        $candidaturas = Hash_votantes::whereIn('hash_votantes.id_votante', $votanteIds)
            ->where('hash_votantes.candidato', 1)
            ->join('eventos', 'hash_votantes.id_evento', '=', 'eventos.id')
            ->select('hash_votantes.id_votante', 'hash_votantes.id_evento', 'eventos.id as evento_id', 'eventos.nombre as evento_nombre')
            ->get()
            ->groupBy('id_votante');

        if (!$nombreEventoActual) {
            $nombreEventoActual = Eventos::where('id', $idEvento)->value('nombre');
        }

        $paginador->getCollection()->transform(function ($item) use ($votaron, $nombresComunas, $candidaturas, $nombreEventoActual) {
            $item->setAttribute('ya_voto', isset($votaron[$item->id_votante]));
            $comunaId = $item->votante ? $item->votante->comuna : null;
            $comunaNombre = $comunaId && isset($nombresComunas[$comunaId]) ? $nombresComunas[$comunaId] : ($comunaId ? "Comuna $comunaId" : 'N/A');
            $item->setAttribute('comuna_nombre', $comunaNombre);

            $candidaturasUser = $candidaturas->get($item->id_votante, collect());
            $eventosCandidato = $candidaturasUser->pluck('evento_nombre')->unique()->values()->toArray();

            // Si está marcado como candidato en este registro, asegurar que el evento actual esté en la lista
            if ($item->candidato == 1 && $nombreEventoActual && !in_array($nombreEventoActual, $eventosCandidato)) {
                array_unshift($eventosCandidato, $nombreEventoActual);
            }

            $item->setAttribute('eventos_candidato', $eventosCandidato);
            $item->setAttribute('es_candidato', ($item->candidato == 1) || (!empty($eventosCandidato)));

            return $item;
        });

        return $paginador;
    }

    /**
     * Retorna el listado paginado y con búsqueda de usuarios asignados a un evento.
     */
    public function listarVotantesEvento(Request $request, $id)
    {
        $search = $request->query('search');
        $estado = $request->query('estado');
        $rol = $request->query('rol');
        $perPage = (int) ($request->query('per_page') ?: 10);
        $perPage = min(max($perPage, 5), 50);

        $query = Hash_votantes::where('id_evento', $id)
            ->with(['evento:id,nombre', 'votante:id,nombre,email,identificacion,tipo_documento,comuna,celular'])
            ->when($search, function ($q, $search) {
                $q->whereHas('votante', function ($sub) use ($search) {
                    $sub->where('nombre', 'like', '%' . $search . '%')
                        ->orWhere('identificacion', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%');
                });
            })
            ->when($estado && $estado !== 'todos', function ($q) use ($estado) {
                $q->where('estado', $estado);
            })
            ->when($rol === 'candidatos', function ($q) {
                $q->where('candidato', 1);
            })
            ->when($rol === 'votantes', function ($q) {
                $q->where('candidato', 0);
            })
            ->orderByRaw('CASE WHEN hash_votantes.candidato = 1 THEN 0 ELSE 1 END ASC')
            ->orderBy('hash_votantes.id', 'desc');

        $paginador = $query->paginate($perPage);

        $paginador = $this->enriquecerVotantes($paginador, $id);

        return response()->json($paginador);
    }

    public function create()
    {
        return Inertia::render(

            'Eventos/Add',

            [
                'tipos' => Tipos::pluck('nombre', 'nombre')->all(),
                'eventos' => Eventos::whereNot('nombre', 'Admin')->where('evento_padre', 1)->get(),
            ]
        );
    }

    public function store(Request $request)
    {

        $this->validate($request, [
            'nombre' => 'required|string|max:255',
            'descripcion' => 'required|string|max:300',
            'dependencias' => 'required|string|max:60',
            'fecha_inicio' => 'required',
            'fecha_fin' => 'required',
            'tipos' => 'required'
        ]);

        $fecha_inicio = Carbon::parse($request->input('fecha_inicio'))->format('Y-m-d H:i:s');
        $fecha_fin = Carbon::parse($request->input('fecha_fin'))->format('Y-m-d H:i:s');

        $eventos = Eventos::create(
            [
                'nombre' => $request->input('nombre'),
                'descripcion' => $request->input('descripcion'),
                'dependencias' => $request->input('dependencias'),
                'tipos' => $request->input('tipos'),
                'evento_padre' => $request->input('is_evento_padre'),
                'fecha_inicio' => $fecha_inicio,
                'fecha_fin' => $fecha_fin,
                'estado' => $request->input('estado'),
            ]
        );

        if ($request->evento_padre) {
            Hash_eventos_hijos::create([
                'id_evento_padre' => $request->evento_padre,
                'id_evento_hijo' => $eventos->id,
            ]);

            $this->actualizarFechasYEstadoPadre($request->evento_padre);
        }

        $eventos->save();

        Cache::forget('eventos_admin');

        return Redirect::route('eventos.index')->with('message', 'Registro almacenado.');
    }

    public function edit(Request $request, $id_user)
    {
        $tipos = Tipos::pluck('nombre', 'nombre')->all();
        $evento = Eventos::with('evento_hijo')->findOrFail($id_user);
        $eventos = Eventos::whereNot('nombre', 'Admin')->whereNot('id', $id_user)->where('evento_padre', 1)->get();

        return Inertia::render('Eventos/Edit', compact('tipos', 'evento', 'eventos'));
    }

    public function update(Request $request, $id_ev)
    {

        $this->validate($request, [
            'nombre' => 'required|string|max:255',
            'descripcion' => 'required|string|max:300',
            'dependencias' => 'required|string|max:60',
            'fecha_inicio' => 'required',
            'fecha_fin' => 'required',
            'tipos' => 'required',
            'estado' => 'required'
        ]);

        $now = Carbon::now();
        $comunas_activas = ParametrosDetalle::where('codParametro', 'com01')
            ->where('estado', 1)
            ->pluck('id')
            ->toArray();


        $fecha_inicio = Carbon::parse($request->input('fecha_inicio'))->format('Y-m-d H:i:s');
        $fecha_fin = Carbon::parse($request->input('fecha_fin'))->format('Y-m-d H:i:s');

        $eventos = Eventos::find($id_ev);
        $eventos->nombre = $request->nombre;
        $eventos->descripcion = $request->descripcion;
        $eventos->dependencias = $request->dependencias;
        $eventos->fecha_inicio = $fecha_inicio;
        $eventos->fecha_fin = $fecha_fin;
        $eventos->tipos = $request->tipos;
        $eventos->evento_padre = $request->is_evento_padre;

        //para detectar cuando es diferente a pendiente
        if ($eventos->estado != 'Pendiente' && $request->estado == 'Pendiente') {

            // El estado ha cambiado a Pendiente, setear aviso_inicio_enviado a 0
            $eventos->aviso_inicio_enviado = 0;
        }

        //para detectar cuando no este activo y cambia de estado a activo
        if ($eventos->estado != 'Activo' && $request->estado == 'Activo') {


            // Crear un acta_inicio asociado al evento
            $acta_inicio = new Acta_inicio();
            $acta_inicio->modalidad = 'virtual';
            $acta_inicio->fecha_inicio = $now;
            $acta_inicio->id_evento = $id_ev;
            $acta_inicio->id_jurado = null; // Asigna el ID del jurado si es necesario
            $acta_inicio->comunas = implode('|', $comunas_activas); // IDs separados por |
            $acta_inicio->puesto_votacion = null; // Asigna el puesto de votación si es necesario
            $acta_inicio->save();
        } else if ($eventos->estado != 'Cerrado' && $request->estado == 'Cerrado') {

            // Crear un acta_fin asociado al evento
            $acta_fin = new Acta_fin();
            $acta_fin->modalidad = 'virtual';
            $acta_fin->fecha_cierre = $now;
            $acta_fin->id_evento = $id_ev;
            $acta_fin->id_jurado = null; // Asigna el ID del jurado si es necesario
            $acta_fin->comunas = implode('|', $comunas_activas); // IDs separados por |
            $acta_fin->puesto_votacion = null; // Asigna el puesto de votación si es necesario
            $acta_fin->save();
        }



        $eventos->estado = $request->estado;
        $eventos->save();

        // Capturar padre anterior antes de modificar el vínculo
        $hash_anterior = Hash_eventos_hijos::where('id_evento_hijo', $eventos->id)->first();
        $id_padre_anterior = $hash_anterior ? $hash_anterior->id_evento_padre : null;

        if ($request->evento_padre) {
            $hash_evento = Hash_eventos_hijos::where('id_evento_hijo', $eventos->id)->first();

            if ($hash_evento) {
                $hash_evento->id_evento_padre = $request->evento_padre;
                $hash_evento->save();
            } else {
                Hash_eventos_hijos::create([
                    'id_evento_padre' => $request->evento_padre,
                    'id_evento_hijo' => $eventos->id,
                ]);
            }
        } else {
            // Si no se selecciona un evento padre, eliminar cualquier registro existente en la tabla hash_eventos_hijos
            Hash_eventos_hijos::where('id_evento_hijo', $eventos->id)->delete();
        }

        // Propagar fechas y estado al/los evento(s) padre afectado(s)
        if ($request->evento_padre) {
            $this->actualizarFechasYEstadoPadre($request->evento_padre);
        }
        if ($id_padre_anterior && $id_padre_anterior != $request->evento_padre) {
            $this->actualizarFechasYEstadoPadre($id_padre_anterior);
        }

        return Redirect::route('eventos.edit', $eventos->id);
    }

    /**
     * Actualiza las fechas de inicio/fin y el estado del evento padre
     * basándose en los eventos hijos actuales (min/max fechas, y Activo si algún hijo está Activo).
     */
    private function actualizarFechasYEstadoPadre($id_padre)
    {
        $padre = Eventos::find($id_padre);
        if (!$padre) {
            return;
        }

        $hijos = Hash_eventos_hijos::where('id_evento_padre', $id_padre)
            ->join('eventos', 'hash_eventos_hijos.id_evento_hijo', '=', 'eventos.id')
            ->select('eventos.fecha_inicio', 'eventos.fecha_fin', 'eventos.estado')
            ->get();

        if ($hijos->isEmpty()) {
            return;
        }

        $min_inicio = $hijos->min('fecha_inicio');
        $max_fin = $hijos->max('fecha_fin');

        $padre->fecha_inicio = $min_inicio;
        $padre->fecha_fin = $max_fin;

        $algun_activo = $hijos->contains(function ($hijo) {
            return $hijo->estado === 'Activo';
        });

        if ($algun_activo && $padre->estado != 'Activo') {
            $now = Carbon::now();

            $comunas_activas = ParametrosDetalle::where('codParametro', 'com01')
                ->where('estado', 1)
                ->pluck('id')
                ->toArray();

            $acta_inicio = new Acta_inicio();
            $acta_inicio->modalidad = 'virtual';
            $acta_inicio->fecha_inicio = $now;
            $acta_inicio->id_evento = $id_padre;
            $acta_inicio->id_jurado = null;
            $acta_inicio->comunas = implode('|', $comunas_activas);
            $acta_inicio->puesto_votacion = null;
            $acta_inicio->save();

            $padre->estado = 'Activo';
        }

        $padre->save();
    }

    /**
     * Muestra la vista para migrar / asignar usuarios de un evento a otro.
     */
    public function migrarUsuariosView(Request $request)
    {
        $eventos = Eventos::whereNot('nombre', 'Admin')
            ->select('id', 'nombre', 'estado', 'fecha_inicio', 'fecha_fin', 'tipos')
            ->orderBy('id', 'desc')
            ->get();

        $comunas = ParametrosDetalle::where('codParametro', 'com01')
            ->select('id', 'detalle')
            ->orderBy('detalle')
            ->get();

        $tipos = Tipos::pluck('nombre', 'nombre')->all();

        return Inertia::render('Eventos/MigrarUsuarios', [
            'eventos' => $eventos,
            'comunas' => $comunas,
            'tipos' => $tipos,
            'origen_inicial' => $request->query('origen') ? (int) $request->query('origen') : null,
            'destino_inicial' => $request->query('destino') ? (int) $request->query('destino') : null,
        ]);
    }

    /**
     * Retorna estadísticas en tiempo real de los votantes de un evento.
     */
    public function estadisticasVotantesEvento(Request $request, $id)
    {
        $origenId = (int) $id;
        $destinoId = $request->query('destino_id') ? (int) $request->query('destino_id') : null;

        $eventoOrigen = Eventos::find($origenId);

        $total = Hash_votantes::where('id_evento', $origenId)->count();
        $soloVotantes = Hash_votantes::where('id_evento', $origenId)->where('candidato', 0)->count();
        $candidatos = Hash_votantes::where('id_evento', $origenId)->where('candidato', 1)->count();
        $activos = Hash_votantes::where('id_evento', $origenId)->where('estado', 'Activo')->count();
        $pendientes = Hash_votantes::where('id_evento', $origenId)->where('estado', 'Pendiente')->count();
        $rechazados = Hash_votantes::where('id_evento', $origenId)->where('estado', 'Rechazado')->count();

        // Tipos presentes en el evento origen
        $tiposOrigen = [];
        if ($eventoOrigen && !empty($eventoOrigen->tipos) && $eventoOrigen->tipos !== 'NA') {
            $tiposOrigen = array_filter(array_map('trim', explode('|', $eventoOrigen->tipos)));
        }
        $tiposEnHashOrigen = Hash_votantes::where('id_evento', $origenId)
            ->whereNotNull('tipo')
            ->where('tipo', '!=', '')
            ->distinct()
            ->pluck('tipo')
            ->flatMap(function ($t) {
                return explode('|', $t);
            })
            ->map(fn($t) => trim($t))
            ->filter()
            ->unique()
            ->values()
            ->toArray();
        $tiposOrigen = array_values(array_unique(array_merge($tiposOrigen, $tiposEnHashOrigen)));

        // Determinar si el evento origen realmente maneja comunas
        $tieneComunas = Hash_votantes::where('id_evento', $origenId)
            ->whereNotNull('subtipo')
            ->where('subtipo', '!=', 0)
            ->where('subtipo', '!=', '0')
            ->where('subtipo', '!=', '')
            ->exists();

        $tiposDestino = [];
        $tiposCoincidentes = [];
        $yaEnDestino = null;
        $nuevos = null;

        if ($destinoId && $destinoId !== $origenId) {
            $eventoDestino = Eventos::find($destinoId);
            if ($eventoDestino && !empty($eventoDestino->tipos) && $eventoDestino->tipos !== 'NA') {
                $tiposDestino = array_filter(array_map('trim', explode('|', $eventoDestino->tipos)));
            }

            // Detectar coincidencias entre origen y destino
            $tiposDestinoLower = array_map('mb_strtolower', $tiposDestino);
            foreach ($tiposOrigen as $tOrig) {
                if (in_array(mb_strtolower($tOrig), $tiposDestinoLower)) {
                    $tiposCoincidentes[] = $tOrig;
                }
            }

            $idsOrigen = Hash_votantes::where('id_evento', $origenId)->pluck('id_votante');
            $yaEnDestino = Hash_votantes::where('id_evento', $destinoId)
                ->whereIn('id_votante', $idsOrigen)
                ->count();
            $nuevos = max(0, $total - $yaEnDestino);
        }

        return response()->json([
            'total' => $total,
            'solo_votantes' => $soloVotantes,
            'candidatos' => $candidatos,
            'activos' => $activos,
            'pendientes' => $pendientes,
            'rechazados' => $rechazados,
            'tiene_comunas' => $tieneComunas,
            'tipos_origen' => $tiposOrigen,
            'tipos_destino' => $tiposDestino,
            'tipos_coincidentes' => $tiposCoincidentes,
            'ya_en_destino' => $yaEnDestino,
            'nuevos' => $nuevos,
        ]);
    }

    /**
     * Ejecuta el proceso de migración o asignación masiva de usuarios entre eventos.
     */
    public function ejecutarMigracionUsuarios(Request $request)
    {
        $request->validate([
            'evento_origen_id' => 'required|integer|exists:eventos,id',
            'evento_destino_id' => 'required|integer|exists:eventos,id|different:evento_origen_id',
            'modo' => 'required|string|in:copiar,mover',
            'filtro_estado' => 'required|string|in:todos,Activo,Pendiente,Rechazado',
            'filtro_tipo' => 'nullable|string',
            'incluir_candidatos' => 'required|boolean',
            'candidatos_como_votantes' => 'nullable|boolean',
            'filtro_comuna' => 'nullable',
            'estado_destino' => 'required|string|in:mantener,Activo,Pendiente',
            'accion_duplicados' => 'required|string|in:omitir,actualizar',
            'resetear_intentos' => 'required|boolean',
        ]);

        $origenId = (int) $request->evento_origen_id;
        $destinoId = (int) $request->evento_destino_id;
        $modo = $request->modo;
        $filtroEstado = $request->filtro_estado;
        $filtroTipo = $request->filtro_tipo;
        $incluirCandidatos = (bool) $request->incluir_candidatos;
        $candidatosComoVotantes = (bool) $request->input('candidatos_como_votantes', true);
        $filtroComuna = $request->filtro_comuna;
        $estadoDestino = $request->estado_destino;
        $accionDuplicados = $request->accion_duplicados;
        $resetearIntentos = (bool) $request->resetear_intentos;

        $eventoOrigen = Eventos::find($origenId);
        $eventoDestino = Eventos::find($destinoId);

        // Validación de coincidencia de tipo con evento destino si se especifica un tipo concreto
        if (!empty($filtroTipo) && $filtroTipo !== 'todos') {
            if ($eventoDestino && !empty($eventoDestino->tipos) && $eventoDestino->tipos !== 'NA') {
                $destTipos = array_map('trim', array_map('mb_strtolower', explode('|', $eventoDestino->tipos)));
                if (!in_array(mb_strtolower(trim($filtroTipo)), $destTipos)) {
                    return redirect()->back()->with('error', "El tipo de votante '{$filtroTipo}' no forma parte de los tipos permitidos en el evento de destino '{$eventoDestino->nombre}' ({$eventoDestino->tipos}).");
                }
            }
        }

        DB::beginTransaction();
        try {
            $query = Hash_votantes::where('id_evento', $origenId);

            if ($filtroEstado !== 'todos') {
                $query->where('estado', $filtroEstado);
            }

            if (!empty($filtroTipo) && $filtroTipo !== 'todos') {
                $query->where('tipo', 'like', '%' . $filtroTipo . '%');
            }

            if (!$incluirCandidatos) {
                $query->where('candidato', 0);
            }

            if (!empty($filtroComuna)) {
                $query->where('subtipo', $filtroComuna);
            }

            $totalEncontrados = $query->count();

            if ($totalEncontrados === 0) {
                DB::rollBack();
                return redirect()->back()->with('error', 'No se encontraron usuarios en el evento de origen con la combinación de filtros seleccionada.');
            }

            $insertados = 0;
            $actualizados = 0;
            $omitidos = 0;
            $noMovidosPorVoto = 0;

            // Procesar por lotes de 500 para optimizar memoria y transacciones masivas
            $query->chunkById(500, function ($votantesChunk) use (
                $destinoId,
                $origenId,
                $modo,
                $estadoDestino,
                $accionDuplicados,
                $resetearIntentos,
                $candidatosComoVotantes,
                &$insertados,
                &$actualizados,
                &$omitidos,
                &$noMovidosPorVoto
            ) {
                $votanteIds = $votantesChunk->pluck('id_votante')->toArray();

                $existentesEnDestino = Hash_votantes::where('id_evento', $destinoId)
                    ->whereIn('id_votante', $votanteIds)
                    ->get()
                    ->keyBy('id_votante');

                $votantesConVoto = [];
                if ($modo === 'mover') {
                    $votantesConVoto = Votos::where('id_eventos', $origenId)
                        ->whereIn('id_votante', $votanteIds)
                        ->pluck('id_votante')
                        ->flip()
                        ->toArray();
                }

                foreach ($votantesChunk as $item) {
                    $votanteId = $item->id_votante;
                    $yaExiste = $existentesEnDestino->has($votanteId);

                    $nuevoEstado = ($estadoDestino === 'mantener') ? $item->estado : $estadoDestino;
                    $nuevosIntentos = $resetearIntentos ? 0 : $item->intentos;

                    // Si era candidato y se seleccionó la opción de migrarlo como un votante más, pasa a ser 0
                    $nuevoCandidato = ($item->candidato == 1 && $candidatosComoVotantes) ? 0 : $item->candidato;

                    if ($modo === 'mover') {
                        // Preservar integridad electoral si el usuario ya votó en el origen
                        if (isset($votantesConVoto[$votanteId])) {
                            $noMovidosPorVoto++;
                            if (!$yaExiste) {
                                Hash_votantes::create([
                                    'id_evento' => $destinoId,
                                    'id_votante' => $votanteId,
                                    'candidato' => $nuevoCandidato,
                                    'tipo' => $item->tipo,
                                    'subtipo' => $item->subtipo,
                                    'motivo' => $item->motivo,
                                    'validaciones' => $item->validaciones,
                                    'estado' => $nuevoEstado,
                                    'intentos' => $nuevosIntentos,
                                    'fisico_info' => $item->fisico_info,
                                ]);
                                $insertados++;
                            } else {
                                $omitidos++;
                            }
                            continue;
                        }

                        if ($yaExiste) {
                            if ($accionDuplicados === 'actualizar') {
                                $existente = $existentesEnDestino->get($votanteId);
                                $existente->update([
                                    'candidato' => $nuevoCandidato,
                                    'tipo' => $item->tipo,
                                    'subtipo' => $item->subtipo,
                                    'estado' => $nuevoEstado,
                                    'intentos' => $nuevosIntentos,
                                ]);
                                $actualizados++;
                            } else {
                                $omitidos++;
                            }
                            $item->delete();
                        } else {
                            $item->update([
                                'id_evento' => $destinoId,
                                'candidato' => $nuevoCandidato,
                                'estado' => $nuevoEstado,
                                'intentos' => $nuevosIntentos,
                            ]);
                            $insertados++;
                        }
                    } else {
                        // Modo copiar (recomendado para nuevos eventos)
                        if ($yaExiste) {
                            if ($accionDuplicados === 'actualizar') {
                                $existente = $existentesEnDestino->get($votanteId);
                                $existente->update([
                                    'candidato' => $nuevoCandidato,
                                    'tipo' => $item->tipo,
                                    'subtipo' => $item->subtipo,
                                    'estado' => $nuevoEstado,
                                    'intentos' => $nuevosIntentos,
                                ]);
                                $actualizados++;
                            } else {
                                $omitidos++;
                            }
                        } else {
                            Hash_votantes::create([
                                'id_evento' => $destinoId,
                                'id_votante' => $votanteId,
                                'candidato' => $nuevoCandidato,
                                'tipo' => $item->tipo,
                                'subtipo' => $item->subtipo,
                                'motivo' => $item->motivo,
                                'validaciones' => $item->validaciones,
                                'estado' => $nuevoEstado,
                                'intentos' => $nuevosIntentos,
                                'fisico_info' => $item->fisico_info,
                            ]);
                            $insertados++;
                        }
                    }
                }
            });

            DB::commit();

            Cache::forget('votantes');
            Cache::forget('eventos_admin');

            $accionVerbo = ($modo === 'mover') ? 'migrados / movidos' : 'asignados / copiados';
            $mensaje = "Proceso completado exitosamente desde '{$eventoOrigen->nombre}' hacia '{$eventoDestino->nombre}'. {$insertados} usuarios {$accionVerbo}";

            if ($actualizados > 0) {
                $mensaje .= ", {$actualizados} actualizados";
            }
            if ($omitidos > 0) {
                $mensaje .= ", {$omitidos} omitidos por ya estar en el evento destino";
            }
            if ($noMovidosPorVoto > 0) {
                $mensaje .= " ({$noMovidosPorVoto} usuarios conservaron su registro en el origen por haber ejercido el voto, pero fueron vinculados al nuevo evento)";
            }

            return redirect()->route('eventos.migrar.view', [
                'origen' => $origenId,
                'destino' => $destinoId
            ])->with('success', $mensaje)->with('resumen', [
                'total' => $totalEncontrados,
                'insertados' => $insertados,
                'actualizados' => $actualizados,
                'omitidos' => $omitidos,
                'no_movidos_por_voto' => $noMovidosPorVoto,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => 'Error al procesar la asignación de usuarios: ' . $e->getMessage()]);
        }
    }
}
