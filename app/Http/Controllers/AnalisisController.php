<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Request as RequestFacade;
use App\Http\Controllers\Controller;
use App\Models\Eventos;
use App\Models\Hash_votantes;
use App\Models\Informacion_votantes;
use App\Models\Tipos;
use App\Models\Votos;
use Illuminate\Http\Request;
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
        $this->middleware('permission:analisis-listar|analisis-crear|analisis-editar|analisis-eliminar', ['only' => ['index', 'store']]);
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
}
