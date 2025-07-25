<?php

// app/Http/Controllers/CertificadosController.php
namespace App\Http\Controllers;

use App\Exports\VotantesVotoExports;
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

        $id_evento = 15;

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
                $query->select('id', 'nombre', 'tipo_documento', 'identificacion', 'genero', 'created_at' ); // agrega aquí solo los campos necesarios
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



        return Inertia::render('VotantesPresencial/Registro', []);
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

        $id_evento = 15;


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
