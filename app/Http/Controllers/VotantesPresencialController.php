<?php

// app/Http/Controllers/CertificadosController.php
namespace App\Http\Controllers;

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

    //register
    public function create(Request $request)
    {



        return Inertia::render('VotantesPresencial/Registro', [

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
}
