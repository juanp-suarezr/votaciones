<?php

// app/Http/Controllers/CertificadosController.php
namespace App\Http\Controllers;

use App\Models\Acta_escrutino;
use App\Models\Acta_fin;
use App\Models\Acta_inicio;
use App\Models\Caninos;
use App\Models\InformacionUsuario;
use App\Models\PageView;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Illuminate\Support\Facades\Request as RequestFacade;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

use App\Models\Delegado;
use App\Models\Delegados;
use App\Models\Eventos;
use App\Models\Hash_proyectos;
use App\Models\Hash_votantes;
use App\Models\Informacion_votantes;
use App\Models\ParametrosDetalle;
use App\Models\Proyectos;
use App\Models\User;
use App\Models\Votos;
use App\Models\Votos_fisicos;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class ActaPresencialController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    function __construct() {}

    
    //index general de reporte escrutinio
    public function index(Request $request)
    {
        $id_evento = 16;


        if (RequestFacade::input('id_evento')) {
            # code...
            $id_evento = RequestFacade::input('id_evento');
        }


        $eventos = Eventos::where('tipos', 'LIKE', '%Proyecto%')->get();
        $actas = Acta_escrutino::when($id_evento, function ($query, $id_evento) {
            $query->where('id_evento',  $id_evento);
        })
            ->when(RequestFacade::input('subtipo'), function ($query, $subtipo) {
                $query->where('comuna',  $subtipo);
            })
            ->when(RequestFacade::input('search'), function ($query, $search) {
                // Si hay búsqueda, filtrar por jurado, pero incluir los que no tienen jurado
                $query->where(function ($q) use ($search) {
                    $q->whereHas('jurado', function ($sub) use ($search) {
                        $sub->where('nombre', 'like', '%' . $search . '%')
                            ->orWhere('identificacion', $search);
                    });
                });
            })
            ->with('jurado')
            ->with('cordinador')
            ->with('votos_fisico.proyecto')
            ->paginate(5)
            ->withQueryString();

        return Inertia::render('VotantesPresencial/Index', [
            'eventos' => $eventos,
            'actas' => $actas,
            'parametros' => ParametrosDetalle::get(),
        ]);
    }

    public function show(Request $request, $id)
    {

        $acta = Acta_escrutino::with('jurado.user.biometrico')
            ->with('votos_fisico.proyecto')
            ->with('evento:id,nombre')
            ->with('cordinador')
            ->findOrFail($id);

        $votos_virtuales_proyectos = [];

        if ($acta->tipo === 'virtual') {
            $votos_virtuales_proyectos = Votos::where('id_eventos', $acta->id_evento)
                ->where('subtipo', $acta->comuna)
                ->where('id_proyecto', '>', 0)
                ->with('proyecto')
                ->get()
                ->groupBy('id_proyecto')
                ->map(function ($votos, $id_proyecto) {
                    $nombre = $votos->first()->proyecto->detalle ?? 'Sin nombre';
                    $cantidad = $votos->count();
                    return [
                        'id_proyecto' => $id_proyecto,
                        'nombre' => $nombre,
                        'cantidad' => $cantidad,
                    ];
                })
                ->values();
        }

        $delegados = Delegados::where('tipo', 'secretario')->get();

        return Inertia::render('VotantesPresencial/Show', [
            'acta' => $acta,
            'parametros' => ParametrosDetalle::where('estado', 1)->get(),
            'delegados' => $delegados,
            'votos_virtuales_proyectos' => $votos_virtuales_proyectos,

        ]);
    }

    //register
    public function create(Request $request)
    {
        
        $jurado = Auth::user()->jurado;



        if ($jurado) {



            $id_evento_padre = $jurado->id_evento;
            $comuna = $jurado->comuna;

            // 1. Obtener eventos hijos con proyectos vigentes
            $eventos_hijos = Eventos::whereHas('eventos_hijos', function ($query) use ($id_evento_padre) {
                $query->where('id_evento_padre', $id_evento_padre);
            })
                ->with(['eventos_hijos.eventos' => function ($query) {
                    $query->whereHas('hash_proyectos');
                }, 'eventos_hijos.eventos.hash_proyectos.proyecto'])
                ->find($id_evento_padre);

            // Extraer los hijos que tienen proyectos vigentes
            $hijos_con_proyectos = collect();
            if ($eventos_hijos && $eventos_hijos->eventos_hijos) {
                foreach ($eventos_hijos->eventos_hijos as $hijo) {

                    // Validar que tenga proyectos
                    if ($hijo->eventos && $hijo->eventos->hash_proyectos->isNotEmpty()) {
                        // Buscar si algún proyecto tiene el mismo subtipo que la comuna
                        $tiene_proyecto_valido = $hijo->eventos->hash_proyectos->contains(function ($hash) use ($comuna) {
                            return optional($hash->proyecto)->subtipo == $comuna;
                        });

                        if ($tiene_proyecto_valido) {
                            if (
                                isset($hijo->eventos) &&
                                $hijo->eventos->hash_proyectos &&
                                $hijo->eventos->hash_proyectos->count() > 0
                            ) {
                                $hijos_con_proyectos->push($hijo->eventos);
                            }
                        }
                    }
                }
            }


            // 2. Obtener las actas enviadas por el jurado para esos eventos hijos
            $actas_enviadas = Acta_escrutino::select('id', 'id_evento', 'id_jurado')
                ->where('id_jurado', $jurado->id)
                ->whereIn('id_evento', collect($hijos_con_proyectos)->pluck('id')->toArray())
                ->get();

            // 3. Comparar la cantidad
            $cantidad_eventos_hijos = count($hijos_con_proyectos);
            $cantidad_actas_enviadas = $actas_enviadas->count();

            // Puedes usar estos datos para mostrar advertencias o controlar el flujo
            // Ejemplo: Si faltan actas por enviar
            $faltan_actas = $cantidad_eventos_hijos - $cantidad_actas_enviadas;


            if ($faltan_actas == 0) {
                return redirect()->route('dashboard', [
                    'error' => true,
                ])->with('error', 'Ya se ha registrado las actas para este jurado, comuna y puesto de votación, en las diferentes vigencias');
            }

            $hijos_sin_actas = $hijos_con_proyectos->filter(function ($hijo) use ($actas_enviadas) {
                return !$actas_enviadas->contains('id_evento', $hijo->id);
            })->values();

            $proyectos_por_evento = Hash_proyectos::whereIn('id_evento', $hijos_sin_actas->pluck('id')->toArray())
                ->whereHas('proyecto', function ($query) use ($jurado) {
                    $query->where('subtipo', $jurado->comuna);
                })
                ->with('proyecto')
                ->get()
                ->groupBy('id_evento')
                ->map(function ($proyectos) {
                    return $proyectos->map(function ($proyecto) {
                        return [
                            'id' => $proyecto->id_proyecto,
                            'nombre' => $proyecto->proyecto->detalle ?? '',
                            'numero_tarjeton' => $proyecto->proyecto->numero_tarjeton ?? '',
                        ];
                    })->values();
                });
        } else {

            

            $id_evento_padre = 15;
            $comuna = $request->comuna;

            // 1. Obtener eventos hijos con proyectos vigentes
            $eventos_hijos = Eventos::whereHas('eventos_hijos', function ($query) use ($id_evento_padre) {
                $query->where('id_evento_padre', $id_evento_padre);
            })
                ->with(['eventos_hijos.eventos' => function ($query) {
                    $query->whereHas('hash_proyectos');
                }, 'eventos_hijos.eventos.hash_proyectos.proyecto'])
                ->find($id_evento_padre);

            // Extraer los hijos que tienen proyectos vigentes
            $hijos_con_proyectos = collect();
            if ($eventos_hijos && $eventos_hijos->eventos_hijos) {
                foreach ($eventos_hijos->eventos_hijos as $hijo) {

                    // Validar que tenga proyectos
                    if ($hijo->eventos && $hijo->eventos->hash_proyectos->isNotEmpty()) {
                        // Buscar si algún proyecto tiene el mismo subtipo que la comuna
                        $tiene_proyecto_valido = $hijo->eventos->hash_proyectos->contains(function ($hash) use ($comuna) {
                            return optional($hash->proyecto)->subtipo == $comuna;
                        });

                        if ($tiene_proyecto_valido) {
                            if (
                                isset($hijo->eventos) &&
                                $hijo->eventos->hash_proyectos &&
                                $hijo->eventos->hash_proyectos->count() > 0
                            ) {
                                $hijos_con_proyectos->push($hijo->eventos);
                            }
                        }
                    }
                }
            }

            
            // 2. Obtener las actas enviadas por el jurado para esos eventos hijos
            $actas_enviadas = Acta_escrutino::select('id', 'id_evento', 'puesto_votacion')
                ->where('puesto_votacion', $request->punto_votacion)
                ->whereIn('id_evento', collect($hijos_con_proyectos)->pluck('id')->toArray())
                ->get();
                

            // 3. Comparar la cantidad
            $cantidad_eventos_hijos = count($hijos_con_proyectos);
            $cantidad_actas_enviadas = $actas_enviadas->count();

            // Puedes usar estos datos para mostrar advertencias o controlar el flujo
            // Ejemplo: Si faltan actas por enviar
            $faltan_actas = $cantidad_eventos_hijos - $cantidad_actas_enviadas;

            
            if ($faltan_actas == 0) {
                return redirect()->route('dashboard', [
                    'error' => true,
                ])->with('error', 'Ya se ha registrado las actas para este evento, comuna y puesto de votación, en las diferentes vigencias');
            }

            $hijos_sin_actas = $hijos_con_proyectos->filter(function ($hijo) use ($actas_enviadas) {
                return !$actas_enviadas->contains('id_evento', $hijo->id);
            })->values();

            $proyectos_por_evento = Hash_proyectos::whereIn('id_evento', $hijos_sin_actas->pluck('id')->toArray())
                ->whereHas('proyecto', function ($query) use ($request) {
                    $query->where('subtipo', $request->comuna);
                })
                ->with('proyecto')
                ->get()
                ->groupBy('id_evento')
                ->map(function ($proyectos) {
                    return $proyectos->map(function ($proyecto) {
                        return [
                            'id' => $proyecto->id_proyecto,
                            'nombre' => $proyecto->proyecto->detalle ?? '',
                            'numero_tarjeton' => $proyecto->proyecto->numero_tarjeton ?? '',
                        ];
                    })->values();
                });
        }

        return Inertia::render('VotantesPresencial/SubirActa', [
            'proyectos' => $proyectos_por_evento,
            'evento' => $jurado ? Eventos::select('id')->find($jurado->id_evento) : 15,
            'comuna' => $jurado ? $jurado->comuna : $request->comuna,
            'puesto_votacion' => $jurado ? $jurado->puntos_votacion : $request->punto_votacion,
            'id_jurado' => $jurado ? $jurado->id : Auth::user()->id,
            'eventos_hijos_vigentes' => $hijos_sin_actas,
            'actas_enviadas' => $actas_enviadas,
            'faltan_actas' => $faltan_actas,
        ]);
    }



    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'id_jurado' => 'required|integer',
            'id_evento' => 'required|integer|exists:eventos,id',
            'comuna' => 'required|integer|exists:parametros_detalle,id',
            'puesto_votacion' => 'required|integer|exists:parametros_detalle,id',
            'fecha_evento' => 'required|date',
            'votos_blancos' => 'required|integer|min:0',
            'votos_nulos' => 'required|integer|min:0',
            'votos_no_marcados' => 'required|integer|min:0',
            'total_ciudadanos' => 'required|integer|min:0',
            'total_votantes' => 'required|integer|min:0',
            'votos_proyectos' => 'required|array',
            'votos_proyectos.*' => 'required|integer|min:0',

            'evidencia' => 'required|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',

        ]);

        DB::beginTransaction();
        try {

            $imgExtension = $request->file('evidencia')->getClientOriginalExtension();
            $folder = 'actas';
            $fileName = 'acta_escrutinio' .$request->id_evento. '_' .$request->id_jurado . '_' . $request->comuna . '_' . $request->puesto_votacion . '.' . $imgExtension;
            // Guardar la imagen
            $imagePath = $request->file('evidencia')->storeAs('uploads/' . $folder, $fileName, 'public');

            // Crear el registro de acta
            $acta = new Acta_escrutino();
            $acta->id_jurado = $request->id_jurado;
            $acta->id_evento = $request->id_evento;
            $acta->comuna = $request->comuna;
            $acta->puesto_votacion = $request->puesto_votacion;
            $acta->fecha_evento = $request->fecha_evento;
            $acta->votos_blanco = $request->votos_blancos;
            $acta->votos_nulos = $request->votos_nulos;
            $acta->votos_no_marcados = $request->votos_no_marcados;
            $acta->total_ciudadanos = $request->total_ciudadanos;
            $acta->total_votantes = $request->total_votantes;
            $acta->imagen = $fileName;
            $acta->observaciones = $request->observaciones;
            $acta->save();

            // Registrar los votos de los proyectos
            foreach ($request->votos_proyectos as $idProyecto => $votos) {
                $votoFisico = new Votos_fisicos();
                $votoFisico->id_acta = $acta->id;
                $votoFisico->id_proyecto = $idProyecto;
                $votoFisico->cantidad = $votos;
                $votoFisico->save();
            }



            DB::commit();
            return redirect()->back()->with('success', [
                'message' => 'Acta de votación registrada exitosamente.',
                'acta_id' => $acta->id,
                'imagen' => Storage::url($imagePath),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al registrar el acta de escrutinio: ' . $e->getMessage());
            return redirect()->back()->withErrors('error', 'Error al registrar el acta de escrutinio.' . $e->getMessage());
        }
    }

    //editar
    public function edit(Request $request, $id)
    {

        $acta = Acta_escrutino::with('jurado')
            ->with('votos_fisico.proyecto')
            ->findOrFail($id);

        return Inertia::render('VotantesPresencial/Show', [
            'acta' => $acta,
            'parametros' => ParametrosDetalle::where('estado', 1)->get(),

        ]);
    }

    public function actaInicial_create()
    {

        $evento_padre = Eventos::where('id', Auth::user()->jurado->id_evento)
            ->with(['eventos_hijos.eventos' => function ($q) {
                $q->whereHas('hash_proyectos'); // solo trae los hijos que tienen proyectos
            }])
            ->first();


        foreach ($evento_padre->eventos_hijos as $event) {

            $acta_inicio = new Acta_inicio();
            $acta_inicio->modalidad = 'presencial';
            $acta_inicio->fecha_inicio = Carbon::now();
            $acta_inicio->id_evento = $event->id_evento_hijo;
            $acta_inicio->id_jurado = Auth::user()->jurado->id; // Asigna el ID del jurado si es necesario
            $acta_inicio->comunas = Auth::user()->jurado->comuna; // IDs separados por |
            $acta_inicio->puesto_votacion = Auth::user()->jurado->puntos_votacion; // Asigna el puesto de votación si es necesario
            $acta_inicio->save();
        }

        return redirect()->back()->with('success', [
            'message' => 'Acta de inicio registrada exitosamente.',
        ]);
    }

    public function actaCerrar_create()
    {

        $evento_padre = Eventos::where('id', Auth::user()->jurado->id_evento)
            ->with(['eventos_hijos.eventos' => function ($q) {
                $q->whereHas('hash_proyectos'); // solo trae los hijos que tienen proyectos
            }])
            ->first();


        foreach ($evento_padre->eventos_hijos as $event) {

            $acta_fin = new Acta_fin();
            $acta_fin->modalidad = 'presencial';
            $acta_fin->fecha_cierre = Carbon::now();
            $acta_fin->id_evento = $event->id_evento_hijo;
            $acta_fin->id_jurado = Auth::user()->jurado->id; // Asigna el ID del jurado si es necesario
            $acta_fin->comunas = Auth::user()->jurado->comuna; // IDs separados por |
            $acta_fin->puesto_votacion = Auth::user()->jurado->puntos_votacion; // Asigna el puesto de votación si es necesario
            $acta_fin->save();
        }

        return redirect()->back()->with('success', [
            'message' => 'Acta de fin registrada exitosamente.',
        ]);
    }
}
