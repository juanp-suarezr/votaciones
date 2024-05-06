<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Request as RequestFacade;
use App\Http\Controllers\Controller;
use App\Models\Eventos;
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

        return Inertia::render(
            'Analisis/Index',
            [

                'eventos' => Eventos::where('estado', '!=', 'Pendiente')
                    ->with('votos')
                    ->get(),
                'candidatos' => Informacion_votantes::where('candidato', 1)
                ->get(),
                'votantes' => Informacion_votantes::whereNot('tipo', 'Admin')
                ->get(),
            ]
        );
    }
}
