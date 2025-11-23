<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Request as RequestFacade;
use App\Http\Controllers\Controller;
use App\Models\Acta_escrutino;
use App\Models\Eventos;
use App\Models\Hash_proyectos;
use App\Models\Hash_votantes;
use App\Models\Informacion_votantes;
use App\Models\ParametrosDetalle;
use App\Models\Tipos;
use App\Models\Votos;
use App\Models\Votos_fisicos;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class AnalisisPresupuestoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    function __construct() {}
    //funcion para mostrar primera pantalla de seleccion de comuna
    function indexComuna()
    {


        return Inertia::render(
            'AnalisisPresupuestoCiudadano/Index',
            []
        );
    }

    //funcion para mostrar pantalla de resultados a la ciudadania

    public function ResultadosPresupuesto(Request $request)
    {
        $id_evento_padre = 15;
        $subtipo = $request->id_comuna;

        $evento_padre = Eventos::where('id', $id_evento_padre)
            ->select('id')
            ->with('eventos_hijos:id,id_evento_hijo,id_evento_padre', 'eventos_hijos.eventos:id,nombre')
            ->first();

        $eventos_hijos = [];
        if ($evento_padre && $evento_padre->eventos_hijos) {
            foreach ($evento_padre->eventos_hijos as $hijo) {
                if (isset($hijo->eventos)) {
                    $eventos_hijos[] = $hijo->eventos;
                }
            }
        }

        $resultados_eventos = [];

        foreach ($eventos_hijos as $evento) {
            // Proyectos del evento y subtipo
            $proyectos = Hash_proyectos::where('id_evento', $evento->id)
                ->whereHas('proyecto', function ($query) use ($subtipo) {
                    $query->where('subtipo', $subtipo);
                })
                ->with(['proyecto' => function ($query) {
                    $query->select('id', 'detalle', 'subtipo');
                }])

                ->get();

                if ($proyectos->isEmpty()) {
                    // Si no hay proyectos para este evento y comuna, saltar al siguiente evento
                    continue;
                }

            // Actas del evento y comuna
            $actas = Acta_escrutino::select('id', 'id_evento', 'comuna', 'votos_nulos', 'votos_no_marcados', 'votos_blanco', 'total_ciudadanos')
                ->where('id_evento', $evento->id)
                ->where('comuna', $subtipo)
                ->get();

            // Sumar votos nulos, no marcados, blanco y total
            $votos_nulos = $actas->sum('votos_nulos');
            $votos_no_marcados = $actas->sum('votos_no_marcados');
            $votos_blanco_fisico = $actas->sum('votos_blanco');
            $total_ciudadanos_fisico = $actas->sum('total_ciudadanos');

            // Votos virtuales y físicos
            $votos_virtuales = Votos::select('id', 'id_eventos', 'id_proyecto', 'subtipo')
                ->where('id_eventos', $evento->id)
                ->where('subtipo', $subtipo)
                ->get();

            $votos_fisicos = Votos_fisicos::whereHas('acta', function ($query) use ($subtipo, $evento) {
                $query->where('id_evento', $evento->id)
                    ->where('comuna', $subtipo);
            })
                ->get();

            // Agrupar y sumar votos por proyecto
            $resultados = [];

            foreach ($proyectos as $proyecto) {
                $resultados[$proyecto->id_proyecto] = [
                    'id_proyecto' => $proyecto->id_proyecto,
                    'nombre' => $proyecto->proyecto->detalle,
                    'votos_virtuales' => 0,
                    'votos_fisicos' => 0,
                    'total' => 0,
                ];
            }

            // Voto en blanco (id_proyecto = 0)
            $resultados[0] = [
                'id_proyecto' => 0,
                'nombre' => 'Voto en blanco',
                'votos_virtuales' => 0,
                'votos_fisicos' => 0,
                'total' => 0,
            ];

            // Sumar votos virtuales
            foreach ($votos_virtuales as $voto) {
                $id = $voto->id_proyecto ?? 0;
                if (!isset($resultados[$id])) {
                    $resultados[$id] = [
                        'id_proyecto' => $id,
                        'nombre' => $id == 0 ? 'Voto en blanco' : 'Proyecto desconocido',
                        'votos_virtuales' => 0,
                        'votos_fisicos' => 0,
                        'total' => 0,
                    ];
                }
                $resultados[$id]['votos_virtuales']++;
            }

            // Sumar votos físicos
            foreach ($votos_fisicos as $votoFisico) {
                $id = $votoFisico->id_proyecto ?? 0;
                if (!isset($resultados[$id])) {
                    $resultados[$id] = [
                        'id_proyecto' => $id,
                        'nombre' => $id == 0 ? 'Voto en blanco' : 'Proyecto desconocido',
                        'votos_virtuales' => 0,
                        'votos_fisicos' => 0,
                        'total' => 0,
                    ];
                }
                $resultados[$id]['votos_fisicos'] += $votoFisico->cantidad;
            }

            // Sumar el voto en blanco físico al resultado del voto en blanco
            $resultados[0]['votos_fisicos'] += $votos_blanco_fisico;

            // Calcular total por proyecto
            foreach ($resultados as &$res) {
                $res['total'] = $res['votos_virtuales'] + $res['votos_fisicos'];
            }
            unset($res);

            // Ordenar de mayor a menor por total (excepto el total general)
            $resultados_ordenados = collect($resultados)
                ->sortByDesc('total')
                ->values()
                ->all();

            // Guardar resultado por evento hijo
            $resultados_eventos[] = [
                'evento_id' => $evento->id,
                'evento_nombre' => $evento->nombre,
                'resultados' => $resultados_ordenados,
                'votos_nulos' => $votos_nulos,
                'votos_no_marcados' => $votos_no_marcados,
                'total_votos' => $total_ciudadanos_fisico+count($votos_virtuales),
            ];
        }

        return Inertia::render(
            'AnalisisPresupuestoCiudadano/VotacionPresupuesto',
            [

                'comuna' => ParametrosDetalle::where('id', $subtipo)->value('detalle'),
                'resultados_eventos' => $resultados_eventos,
            ]
        );
    }

    function index()
    {

        $eventos = Eventos::select('id', 'tipos', 'estado', 'nombre')
            ->where('estado', '!=', 'Pendiente')
            ->whereHas('votos') // Solo eventos con al menos un voto
            ->orWhereHas('acta_escrutinio') // Solo eventos con al menos un voto fisico
            ->get()
            ->filter(function ($evento) {
                // Filtra eventos cuyo tipo contiene "proyectos" (mayúsculas o minúsculas)
                Log::info(stripos($evento->tipos, 'presupuesto participativo'));
                return stripos($evento->tipos, 'presupuesto participativo') !== false;
            })
            ->values();


        return Inertia::render(
            'AnalisisPresupuesto/Index',
            [

                'eventos' => $eventos,

            ]
        );
    }

    function ResultadosComunas(Request $request)
    {

        //variables
        $subtipo = null;
        $id_evento_hijo = 16;
        $votos_virtuales = [];
        $votos_fisicos = [];



        if (RequestFacade::input('subtipo')) {
            # code...
            $subtipo = RequestFacade::input('subtipo');
        }

        if (RequestFacade::input('id_evento_hijo')) {
            # code...
            $id_evento_hijo = RequestFacade::input('id_evento_hijo');
        }

        $evento = Eventos::where('id', $id_evento_hijo)
            ->with([
                'votos:id,id_eventos,isVirtual,subtipo,id_votante',
                'acta_escrutinio:id,id_evento,comuna,total_ciudadanos,total_votantes',
                'evento_hijo' => function ($query) use ($request) {
                    $query->select('id', 'id_evento_padre', 'id_evento_hijo')
                        ->with(['evento_padre' => function ($q) {
                            $q->withCount(['votantes as votantes_activos_count' => function ($q2) {
                                $q2->where('estado', 'Activo')
                                    ->orWhere('validaciones', 'voto presencial - virtual');
                            }]);
                        }]);
                }
            ])
            ->first();




        // Leer comunas completas desde el JSON
        $comunas_completas = json_decode(file_get_contents(resource_path('js/shared/comunas_completas.json')), true);

        // IDs de comunas con votos físicos
        $comunas_fisicas_ids = $evento->acta_escrutinio->pluck('comuna')->unique()->filter()->values()->all();

        // IDs de comunas con votos virtuales (si el voto tiene comuna asociada)
        $comunas_virtuales_ids = $evento->votos->pluck('subtipo')->unique()->filter()->values()->all();

        // Unir y obtener comunas únicas
        $comunas_ids = collect($comunas_fisicas_ids)
            ->merge($comunas_virtuales_ids)
            ->unique()
            ->filter()
            ->values()
            ->all();



        if ($subtipo != null || $subtipo != '') {
            $comunas_ids = [0 => $subtipo];
        }

        // Filtrar comunas completas solo con las que tienen votos
        $comunas_con_votos = collect($comunas_completas)
            ->whereIn('value', array_map('strval', $comunas_ids))
            ->values()
            ->all();

        // Contar votos virtuales y físicos por comuna
        $resumen_comunas = [];
        foreach ($comunas_con_votos as $comuna) {
            $idComuna = $comuna['value'];

            // Votos físicos: sumar total_ciudadanos de todas las actas de esa comuna
            $votos_fisicos = $evento->acta_escrutinio
                ->where('comuna', $idComuna)
                ->sum('total_votantes');

            // Votos virtuales: contar votos con esa comuna
            $votos_virtuales = $evento->votos->where('subtipo', $idComuna)->where('isVirtual', 1)->count();
            $votos_presenciales_tic = $evento->votos->where('subtipo', $idComuna)->where('isVirtual', 0)->count();

            $resumen_comunas[] = [
                'label' => $comuna['label'],
                'value' => $idComuna,
                'votos_fisicos' => $votos_fisicos,
                'votos_virtuales' => $votos_virtuales,
                'votos_presenciales_Tic' => $votos_presenciales_tic
            ];
        }

        // Número de elementos por página
        $perPage = 5;
        $page = request('page', 1);

        // Convierte el array a colección y pagina
        $collection = collect($resumen_comunas);
        $paginated = new LengthAwarePaginator(
            $collection->forPage($page, $perPage)->values(),
            $collection->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        $evento_padre = Eventos::where('id', $request->id_evento)
            ->select('id')
            ->with('eventos_hijos:id,id_evento_hijo,id_evento_padre', 'eventos_hijos.eventos:id,nombre')
            ->first();

        // Extraer la lista de hijos (solo el objeto 'eventos' de cada hijo)
        $eventos_hijos = [];
        if ($evento_padre && $evento_padre->eventos_hijos) {
            foreach ($evento_padre->eventos_hijos as $hijo) {
                if (isset($hijo->eventos)) {
                    $eventos_hijos[] = $hijo->eventos;
                }
            }
        }





        return Inertia::render(
            'AnalisisPresupuesto/PresupuestoParticipativo',
            [

                'evento' => $evento,
                'resultados' => $paginated,
                'eventos_hijos' => $eventos_hijos,

            ]
        );
    }

    function ResultadosProyectos(Request $request)
    {

        // 1. Traer los proyectos del evento y subtipo (comuna)
        $proyectos = Hash_proyectos::where('id_evento', $request->id_evento)
            ->whereHas('proyecto', function ($query) use ($request) {
                $query->where('subtipo', $request->subtipo);
            })
            ->with(['proyecto' => function ($query) {
                $query->select('id', 'detalle', 'subtipo');
            }])
            ->with(['evento.evento_hijo.evento_padre.votantes' => function ($query) use ($request) {
                $query->where('subtipo', $request->subtipo) // Requerimiento fundamental
                    ->where(function ($q) {
                        $q->where('estado', 'Activo')
                            ->orWhere('validaciones', 'voto presencial - virtual');
                    });
            }])
            ->get();

        $total_votantes_virtual = 0;



        //actas del evento y comuna
        $actas = Acta_escrutino::select('id', 'id_evento', 'comuna', 'votos_nulos', 'votos_no_marcados', 'votos_blanco', 'total_ciudadanos')
            ->where('id_evento', $request->id_evento)
            ->where('comuna', $request->subtipo)
            ->get();

        // Sumar votos nulos y no marcados de todas las actas
        $votos_nulos = $actas->sum('votos_nulos');
        $votos_no_marcados = $actas->sum('votos_no_marcados');
        $votos_blanco_fisico = $actas->sum('votos_blanco'); // Voto en blanco físico en actas
        //suma total de ciudadanos
        $total_ciudadanos_fisico = $actas->sum('total_ciudadanos');



        $votos_virtuales = Votos::select('id', 'id_eventos', 'id_proyecto', 'subtipo')
            ->where('id_eventos', $request->id_evento)
            ->where('subtipo', $request->subtipo)
            ->get();

        $votos_fisicos = Votos_fisicos::whereHas('acta', function ($query) use ($request) {
            $query->where('id_evento', $request->id_evento)
                ->where('comuna', $request->subtipo);
        })
            ->get();

        // 4. Agrupar y sumar votos por proyecto
        $resultados = [];

        // Inicializar resultados con los proyectos
        foreach ($proyectos as $proyecto) {

            $total_votantes_virtual += $proyecto->evento->evento_hijo[0]->evento_padre->votantes->count();

            $resultados[$proyecto->id_proyecto] = [
                'id_proyecto' => $proyecto->id_proyecto,
                'nombre' => $proyecto->proyecto->detalle,
                'votos_virtuales' => 0,
                'votos_fisicos' => 0,
                'total' => 0,
            ];
        }



        // Voto en blanco (id_proyecto = 0)
        $resultados[0] = [
            'id_proyecto' => 0,
            'nombre' => 'Voto en blanco',
            'votos_virtuales' => 0,
            'votos_fisicos' => 0,
            'total' => 0,
        ];

        // Sumar votos virtuales
        foreach ($votos_virtuales as $voto) {
            $id = $voto->id_proyecto ?? 0;
            if (!isset($resultados[$id])) {
                // Si por alguna razón hay un id_proyecto no registrado, lo agregamos
                $resultados[$id] = [
                    'id_proyecto' => $id,
                    'nombre' => $id == 0 ? 'Voto en blanco' : 'Proyecto desconocido',
                    'votos_virtuales' => 0,
                    'votos_fisicos' => 0,
                    'total' => 0,
                ];
            }
            $resultados[$id]['votos_virtuales']++;
        }

        // Sumar votos físicos
        foreach ($votos_fisicos as $votoFisico) {
            $id = $votoFisico->id_proyecto ?? 0;
            if (!isset($resultados[$id])) {
                $resultados[$id] = [
                    'id_proyecto' => $id,
                    'nombre' => $id == 0 ? 'Voto en blanco' : 'Proyecto desconocido',
                    'votos_virtuales' => 0,
                    'votos_fisicos' => 0,
                    'total' => 0,
                ];
            }
            $resultados[$id]['votos_fisicos'] += $votoFisico->cantidad;
        }

        // Sumar el voto en blanco físico al resultado del voto en blanco
        $resultados[0]['votos_fisicos'] += $votos_blanco_fisico;
        $resultados[0]['total'] = $resultados[0]['votos_virtuales'] + $resultados[0]['votos_fisicos'];

        // Calcular total y ordenar
        foreach ($resultados as &$res) {
            $res['total'] = $res['votos_virtuales'] + $res['votos_fisicos'];
        }
        unset($res);

        // Ordenar de mayor a menor por total
        $resultados = collect($resultados)->sortByDesc('total')->values()->all();



        return Inertia::render(
            'AnalisisPresupuesto/VotacionProyectos',
            [
                'id_evento' => $request->id_evento,
                'subtipo' => $request->subtipo,
                'comuna' => $request->comuna,
                'proyectos' => $resultados,
                'votos_nulos' => $votos_nulos,
                'votos_no_marcados' => $votos_no_marcados,
                'total_votos_virtuales' => $total_votantes_virtual,
                'total_votos_fisicos' => $total_ciudadanos_fisico

            ]
        );
    }

    function ResultadosGenerales(Request $request)
    {
        //actas del evento y comuna
        $actas = Acta_escrutino::select('id', 'id_evento', 'comuna', 'votos_nulos', 'votos_no_marcados', 'votos_blanco', 'puesto_votacion', 'total_ciudadanos')
            ->where('id_evento', $request->id_evento)
            ->where('comuna', $request->subtipo)
            ->with(['votos_fisico', 'puntos_votacion_rp'])
            ->get();

        //evento para determinar total registros virtuales y tic
        $evento = Eventos::where('id', $request->id_evento)
            ->with(['evento_hijo.evento_padre.votantes' => function ($query) use ($request) {
                $query->where('subtipo', $request->subtipo) // Requerimiento fundamental
                    ->where(function ($q) {
                        $q->where('estado', 'Activo');
                    });
            }])
            ->first();


        //suma total de ciudadanos
        $total_registros_virtuales = $evento->evento_hijo[0]->evento_padre->votantes->count();

        $resumen_puestos = [];
        $total_registros_fisicos = 0;
        foreach ($actas as $acta) {
            $total_registros_fisicos += $acta->total_ciudadanos;
            $puesto_id = $acta->puesto_votacion ?? 0;

            $puesto_nombre = $acta->puntos_votacion_rp->detalle ?? 'Sin puesto';

            // Sumar votos_fisico asociados a esta acta
            $votos_fisicos = $acta->votos_fisico->sum('cantidad');

            // Inicializar si no existe
            if (!isset($resumen_puestos[$puesto_id])) {
                $resumen_puestos[$puesto_id] = [
                    'puesto_id' => $puesto_id,
                    'puesto_nombre' => $puesto_nombre,
                    'votos_fisicos' => 0,
                ];
            }

            $resumen_puestos[$puesto_id]['votos_fisicos'] += $acta->votos_nulos + $acta->votos_no_marcados + $acta->votos_blanco + $votos_fisicos;
        }

        // Si quieres un array indexado:
        $resumen_puestos = array_values($resumen_puestos);


        // Sumar votos nulos y no marcados de todas las actas
        $votos_nulos = $actas->sum('votos_nulos');
        $votos_no_marcados = $actas->sum('votos_no_marcados');
        $votos_blanco_fisico = $actas->sum('votos_blanco'); // Voto en blanco físico en actas


        $votos_virtuales = Votos::select('id', 'id_eventos', 'id_proyecto', 'subtipo', 'isVirtual')
            ->where('id_eventos', $request->id_evento)
            ->where('subtipo', $request->subtipo)
            ->where('isVirtual', 1)
            ->get();

        $total_votos_virtuales = $votos_virtuales->count();

        $votos_virtuales_tic = Votos::select('id', 'id_eventos', 'id_proyecto', 'subtipo', 'isVirtual', 'id_votante')
            ->where('id_eventos', $request->id_evento)
            ->where('subtipo', $request->subtipo)
            ->where('isVirtual', 0)
            ->with('votante.jurado')
            ->get();

        $total_votos_virtuales_tic = $votos_virtuales_tic->count();

        $votos_fisicos = Votos_fisicos::whereHas('acta', function ($query) use ($request) {
            $query->where('id_evento', $request->id_evento)
                ->where('comuna', $request->subtipo);
        })
            ->get();

        // Agrupar y contar por punto de votación
        $votos_por_punto_virtual_tic = $votos_virtuales_tic->groupBy(function ($voto) {
            // Navega por las relaciones para obtener el nombre o id del punto de votación

            return $voto->votante->jurado->puntos_votacion ?? 0;
        })->map(function ($group) {

            return $group->count();
        });



        // Mezclar votos TIC con los puestos físicos
        foreach ($resumen_puestos as &$puesto) {
            // Busca los votos TIC por nombre de puesto
            $puesto['votos_tic'] = $votos_por_punto_virtual_tic[$puesto['puesto_id']] ?? 0;
        }
        // Agregar los puestos que solo tienen votos TIC y no físicos
        foreach ($votos_por_punto_virtual_tic as $id_puesto => $cantidad_tic) {
            // Si el puesto no existe en $resumen_puestos, lo agregamos
            $existe = collect($resumen_puestos)->contains(function ($p) use ($id_puesto) {
                return $p['puesto_id'] === $id_puesto;
            });
            if (!$existe) {
                $resumen_puestos[] = [
                    'puesto_id' => $id_puesto,
                    'puesto_nombre' => 'Punto no referenciado',
                    'votos_fisicos' => 0,
                    'votos_tic' => $cantidad_tic,
                ];
            }
        }

        // Opcional: reindexar el array
        $resumen_puestos = array_values($resumen_puestos);




        $votos_blanco_virtual = $votos_virtuales->where('id_proyecto', 0)->count();
        $votos_blanco_tic = $votos_virtuales_tic->where('id_proyecto', 0)->count();
        $voto_validado = ($votos_virtuales->count() - $votos_blanco_virtual) + ($votos_virtuales_tic->count() - $votos_blanco_tic);
        $total_votos_fisicos = $votos_nulos + $votos_no_marcados + $votos_blanco_fisico;
        foreach ($votos_fisicos as $votoFisico) {

            $total_votos_fisicos += $votoFisico->cantidad;
            //sumar votos validados fisico sin contar el voto en blanco, nuo o no marcado
            $voto_validado += $votoFisico->cantidad;
        }



        // Total votos en blanco (virtuales + TIC + físicos de actas)
        $total_votos_blanco = $votos_blanco_virtual + $votos_blanco_tic + $votos_blanco_fisico;




        return Inertia::render(
            'AnalisisPresupuesto/VotacionGeneral',
            [
                'comuna' => $request->comuna,
                'votos_nulos' => $votos_nulos,
                'votos_no_marcados' => $votos_no_marcados,
                'votos_blanco' => $total_votos_blanco,
                'votos_validados' => $voto_validado,
                'resumen_puestos' => $resumen_puestos,
                'votos_virtuales' => $total_votos_virtuales,
                'votos_virtuales_tic' => $total_votos_virtuales_tic,
                'votos_fisicos' => $total_votos_fisicos,
                'total_votos_fisicos' => $total_registros_fisicos,
                'total_votos_virtuales' => $total_registros_virtuales,
            ]
        );
    }
}
