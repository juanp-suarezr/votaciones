<?php

// app/Http/Controllers/CertificadosController.php
namespace App\Http\Controllers;

use App\Models\Acta_escrutino;
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

    //index
    public function index(Request $request)
    {
        // $eventos = Eventos::where('estado', 'Activo')->get();


        // return Inertia::render('ActaPresencial/Index', [
        //     'eventos' => $eventos,
        // ]);
    }

    //register
    public function create(Request $request)
    {
        //validacion si ya subio el acta
        $actaExistente = Acta_escrutino::where('id_jurado', Auth::user()->jurado->id)
            ->where('comuna', Auth::user()->jurado->comuna)
            ->where('puesto_votacion', Auth::user()->jurado->puntos_votacion)
            ->first();

        if ($actaExistente) {
             return redirect()->route('dashboard', [
                'error' => true,
            ])->with('error', 'Ya se ha registrado un acta para este jurado, comuna y puesto de votación.');

        }

        //proyectos
        $proyectos = Hash_proyectos::where('id_evento', Auth::user()->jurado->id_evento)
            ->whereHas('proyecto', function ($query) {
                $query->where('subtipo', Auth::user()->jurado->comuna);
            })
            ->with(['proyecto'])
            ->get();

        return Inertia::render('VotantesPresencial/SubirActa', [
            'proyectos' => $proyectos,
            'evento' => Eventos::select('id')->find(Auth::user()->jurado->id_evento),
            'comuna' => Auth::user()->jurado->comuna,
            'puesto_votacion' => Auth::user()->jurado->puntos_votacion,
            'id_jurado' => Auth::user()->jurado->id,
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
            'id_jurado' => 'required|integer|exists:delegados,id',
            'id_evento' => 'required|integer|exists:eventos,id',
            'comuna' => 'required|integer|exists:parametros_detalle,id',
            'puesto_votacion' => 'required|integer|exists:parametros_detalle,id',
            'nombre_testigo' => 'required|string|max:155',
            'Identification_testigo' => 'required|string|max:20',
            'contacto_testigo' => 'required|string|max:20',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
            'hora_inicio' => 'required|string',
            'hora_cierre' => 'required|string|after_or_equal:hora_inicio',
            'votos_blancos' => 'required|integer|min:0',
            'votos_nulos' => 'required|integer|min:0',
            'votos_no_marcados' => 'required|integer|min:0',
            'total_ciudadanos' => 'required|integer|min:0',
            'votos_proyectos' => 'required|array',
            'votos_proyectos.*' => 'required|integer|min:0',

            'evidencia' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',

        ]);

        DB::beginTransaction();
        try {

            $imgExtension = $request->file('evidencia')->getClientOriginalExtension();
            $folder = 'actas';
            $fileName = 'acta_escrutinio' . $request->id_jurado. '_'.$request->comuna.'_'.$request->puesto_votacion . '.' . $imgExtension;
            // Guardar la imagen
            $imagePath = $request->file('evidencia')->storeAs('uploads/' . $folder, $fileName, 'public');

            // Crear el registro de acta
            $acta = new Acta_escrutino();
            $acta->id_jurado = $request->id_jurado;
            $acta->id_evento = $request->id_evento;
            $acta->comuna = $request->comuna;
            $acta->puesto_votacion = $request->puesto_votacion;
            $acta->nombre_testigo = $request->nombre_testigo;
            $acta->Identificacion_testigo = $request->Identification_testigo;
            $acta->contacto_testigo = $request->contacto_testigo;
            $acta->fecha_inicio = $request->fecha_inicio;
            $acta->fecha_fin = $request->fecha_fin;
            $acta->hora_inicio = $request->hora_inicio;
            $acta->hora_cierre = $request->hora_cierre;
            $acta->votos_blanco = $request->votos_blancos;
            $acta->votos_nulos = $request->votos_nulos;
            $acta->votos_no_marcados = $request->votos_no_marcados;
            $acta->total_ciudadanos = $request->total_ciudadanos;
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
}
