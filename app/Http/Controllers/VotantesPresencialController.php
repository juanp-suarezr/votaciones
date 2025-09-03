<?php

// app/Http/Controllers/CertificadosController.php
namespace App\Http\Controllers;

use App\Exports\VotantesVotoExports;
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
use Maatwebsite\Excel\Facades\Excel;

use App\Models\Delegado;
use App\Models\Delegados;
use App\Models\Eventos;
use App\Models\Hash_votantes;
use App\Models\Informacion_votantes;
use App\Models\ParametrosDetalle;
use App\Models\User;
use App\Models\Votos;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class VotantesPresencialController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    function __construct() {}

    //index para mostrar el reporte de todos los votantes
    public function index()
    {

        $id_evento = 16;
        $anio_actual = Carbon::now()->year;

        if (Auth::user()->jurado) {

            $id_evento = Auth::user()->jurado->id_evento;
        } else {

            if (RequestFacade::input('id_evento')) {
                # code...
                $id_evento = Eventos::select('id')->where('id', RequestFacade::input('id_evento'))->first();
                $id_evento = $id_evento->id;
            }
        }



        $votantes = Votos::select(
            'id_votante',
            'id_eventos',
            'subtipo',
            'created_at',
            'updated_at',
            'estado',
        )
            ->whereYear('created_at', $anio_actual)
            ->where('id_eventos', $id_evento)
            ->when(RequestFacade::input('subtipo'), function ($query, $subtipo) {
                $query->where('subtipo',  $subtipo);
            })
            ->whereHas('votante', function ($query) {
                $query->when(RequestFacade::input('search'), function ($query, $search) {
                    $query->where('nombre', 'like', '%' . $search . '%')
                        ->orWhere('identificacion', 'like', '%' . $search . '%');
                });
            })
            ->with('votante')->with(['votante' => function ($query) {
                $query->select('id', 'nombre', 'tipo_documento', 'identificacion', 'genero', 'created_at'); // agrega aquí solo los campos necesarios
            }])
            ->paginate(5)
            ->withQueryString(); // Mantener los parámetros en la URL

        return Inertia::render('ReporteVotantes/Index', [
            'votantes_voto' => $votantes,
            'eventos' => Eventos::select('id', 'nombre')->whereHas('votos')->get(),
            'filters' => RequestFacade::only(['search', 'id_evento', 'subtipo']),
        ]);
    }

    //register
    public function create(Request $request)
    {
        $existeActa = null;
        $cierre = null;
        $evento_padre = Eventos::where('id', Auth::user()->jurado->id_evento)
            ->with(['eventos_hijos.eventos' => function ($q) {
                $q->whereHas('hash_proyectos'); // solo trae los hijos que tienen proyectos
            }])
            ->first();

        foreach ($evento_padre->eventos_hijos as $event) {

            if ($existeActa === false) {
                continue;
            }
            if ($cierre === false) {
                continue;
            }
            $existeActa = Acta_inicio::where('id_jurado', Auth::user()->jurado->id)
                ->where('id_evento', $event->id_evento_hijo)
                ->exists();


            $cierre = Acta_fin::where('id_jurado', Auth::user()->jurado->id)
                ->where('id_evento', $event->id_evento_hijo)
                ->exists();
        }

        if ($existeActa === false) {
            return redirect()->back()->withErrors('error', 'Error al registrar el votante.');
        }

        if ($cierre === true) {
            return redirect()->back()->withErrors('error', 'Error al registrar el votante.');
        }



        return Inertia::render('VotantesPresencial/Registro', []);
    }

    //mostrar eventos de votacion
    public function showEventos(Request $request)
    {

        $id_evento_padre = Auth::user()->jurado->id_evento;
        $id_votante = $request->id_votante;

        // Traer evento padre con hijos activos y proyectos vigentes
        $evento_padre = Eventos::where('id', $id_evento_padre)
            ->with(['eventos_hijos' => function ($q) {
                $q->whereHas('eventos', function ($q2) {
                    $q2->whereHas('hash_proyectos') // hijos con proyectos
                        ->where('estado', 'Activo'); // hijos activos
                })->with(['eventos' => function ($q2) {
                    $q2->whereHas('hash_proyectos')
                        ->where('estado', 'Activo');
                }]);
            }])
            ->first();

        // Filtrar hijos que NO tengan votaciones realizadas por este votante
        $eventos_hijos_sin_voto = collect($evento_padre->eventos_hijos ?? [])
            ->filter(function ($hijo) use ($id_votante) {
                if ($hijo->id_evento_hijo) {
                    $ya_voto = Votos::where('id_eventos', $hijo->id_evento_hijo)
                        ->where('id_votante', $id_votante)
                        ->exists();
                    return !$ya_voto;
                }
                return false;
            })
            ->values();








        $votante = Informacion_votantes::select('id', 'identificacion', 'nombre', 'id_user')
            ->where('id', $id_votante)
            ->where('comuna', '!=', 0)
            ->whereNotNull('comuna')
            ->with('hashVotantes')
            ->first();

        return Inertia::render('VotantesPresencial/Eventos', [
            'eventos_hijos' => $eventos_hijos_sin_voto,
            'votante' => $votante
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
            'nombre' => 'required|string|max:255',
            'identificacion' => 'required|string|max:20',
            Rule::unique('informacion_votantes')->where(function ($query) {
                return $query->whereNotNull('comuna')->where('comuna', '!=', 0);
            }),
            'tipo_documento' => 'required|string',
            'nacimiento' => 'required|date',
            'genero' => 'required|string',
            'etnia' => 'required|string',
            'condicion' => 'required|string',
            'barrio' => 'string',
            'direccion' => 'string',
        ]);

        DB::beginTransaction();
        try {

            // Crear info votante presencial
            $infoVotante = Informacion_votantes::create([
                'id_jurado' => $request->id_jurado,
                'nombre' => $request->nombre,
                'identificacion' => $request->identificacion,
                'tipo_documento' => $request->tipo_documento,
                'nacimiento' => Carbon::parse($request->nacimiento)->format('Y-m-d'),
                'genero' => $request->genero,
                'etnia' => $request->etnia,
                'condicion' => $request->condicion,
                'barrio' => $request->barrio,
                'direccion' => $request->direccion,
                'comuna' => $request->comuna,
            ]);

            // crear hash votante
            $hashVotante = Hash_votantes::create([
                'id_votante' => $infoVotante->id,
                'id_evento' => $request->id_evento,
                'tipo' => 'Votante',
                'subtipo' => $request->comuna,
                'candidato' => 0, // Asignar 0 para indicar que no es candidato
                'validaciones' => 'voto presencial - virtual',
                'estado' => 'Activo',
                'intentos' => 0,
            ]);

            DB::commit();
            return redirect()->back()->with('success', [
                'id_votante' => $infoVotante->id,
                'evento_id' => $request->id_evento,
                'hash_votante' => $hashVotante,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al registrar jurado: ' . $e->getMessage());
            return redirect()->back()->withErrors('error', 'Error al registrar el votante.' . $e->getMessage());
        }
    }

    public function excel()
    {
        ob_end_clean();
        ob_start();

        $id_evento = 16;


        if (Auth::user()->jurado) {

            $id_evento = Auth::user()->jurado->id_evento;
        } else {

            $id_evento = Eventos::select('id')->where('id', RequestFacade::input('id_evento'))->first();
        }


        $subtipo = RequestFacade::input('subtipo');
        $search = RequestFacade::input('search');




        return Excel::download(new VotantesVotoExports($id_evento, $subtipo, $search), 'votantes_voto.xls', \Maatwebsite\Excel\Excel::XLS);
    }
}
