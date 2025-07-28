<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Request as RequestFacade;
use App\Http\Controllers\Controller;
use App\Models\Eventos;
use App\Models\Hash_proyectos;
use App\Models\Hash_votantes;
use App\Models\Informacion_votantes;
use App\Models\Tipos;
use App\Models\Votos;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class AnalisisController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    function __construct()
    {
        $this->middleware('permission:analisis-listar|analisis-crear|analisis-editar|analisis-eliminar', ['only' => ['index', 'analisisPresupuesto']]);
        $this->middleware('permission:analisis-crear', ['only' => ['create', 'store']]);
        $this->middleware('permission:analisis-editar', ['only' => ['edit', 'update']]);
        $this->middleware('permission:analisis-eliminar', ['only' => ['destroy']]);
    }
    function index()
    {

        $eventos = Eventos::where('estado', '!=', 'Pendiente')
            ->whereNot('nombre', 'Admin')
            ->with('votos')
            ->get()
            ->filter(function ($evento) {
                // Filtra eventos cuyo tipo contiene "proyectos" (mayúsculas o minúsculas)
                return stripos($evento->tipos, 'Presupuesto Participativo') === false;
            })
            ->values();

        return Inertia::render(
            'Analisis/Index',
            [

                'eventos' => $eventos,
                'candidatos' => Hash_votantes::where('candidato', 1)->with('votante')
                    ->get(),
                'votantes' => Hash_votantes::whereNot('tipo', 'Admin')
                    ->whereHas('votante.user.roles', function ($query) {
                        $query->where('name', '!=', 'votoBlanco'); // Excluye roles con 'votoBlanco'
                    })
                    ->with(['votante.user.roles']) // Carga las relaciones necesarias
                    ->get(),
            ]
        );
    }

    function analisisPresupuesto()
    {

        //variables
        $id_evento = 15;
        $subtipo = 1;
        $votos_virtuales = [];
        $votos_fisicos = [];

        if (RequestFacade::input('id_evento')) {
            # code...
            $id_evento = RequestFacade::input('id_evento');
        }
        if (RequestFacade::input('subtipo')) {
            # code...
            $subtipo = RequestFacade::input('subtipo');
        }

        $eventos = Eventos::where('estado', '!=', 'Pendiente')
            ->whereNot('nombre', 'Admin')
            ->with('votos')
            ->get()
            ->filter(function ($evento) {
                // Filtra eventos cuyo tipo contiene "proyectos" (mayúsculas o minúsculas)
                return stripos($evento->tipos, 'Presupuesto Participativo') === true;
            })
            ->values();

        $proyectos = Hash_proyectos::when($id_evento, function ($query, $id_evento) {
            $query->where('id_evento',  $id_evento);
        })
            ->whereHas('proyecto', function ($query) use ($subtipo) {
                $query->when($subtipo, function ($query, $subtipo) {
                    $query->where('subtipo', $subtipo);
                });
            })->get();




        $votos_virtuales = Votos::select('id', 'id_eventos', 'id_proyecto', 'subtipo', 'isVirtual')
        ->where('id_eventos', $id_evento)
        ->get();

        $votos_fisicos = Votos::select('id', 'id_eventos', 'id_proyecto', 'subtipo', 'isVirtual')
        ->where('id_eventos', $id_evento)
        ->get();


        return Inertia::render(
            'Analisis/PresupuestoParticipativo',
            [

                'eventos' => $eventos,
                'proyectos' => $proyectos,
                'votos_virtuales' => $votos_virtuales,
                'votos_fisicos' => $votos_fisicos,
            ]
        );
    }
}
