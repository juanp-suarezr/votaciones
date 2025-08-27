<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\RutasVotaciones;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Request as RequestFacade;

class RutasController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    function __construct()
    {
        $this->middleware('permission:rutas-listar|rutas-crear|rutas-editar|rutas-eliminar', ['only' => ['index', 'store']]);
        $this->middleware('permission:rutas-crear', ['only' => ['create', 'store']]);
        $this->middleware('permission:rutas-editar', ['only' => ['edit', 'update']]);
        $this->middleware('permission:rutas-eliminar', ['only' => ['destroy']]);
    }

    public function index()
    {
        $rutas = RutasVotaciones::select('id', 'path', 'fecha_inicio', 'fecha_fin', 'estado', 'created_at')
            ->orderBy('created_at', 'desc')
            ->paginate(5)
            ->withQueryString(); // Mantener los parámetros en la URL
        return Inertia::render(
            'Rutas/Index',
            [

                'rutas' => $rutas,
                'filters' => RequestFacade::only(['search']),

            ]
        );
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return Inertia::render('Rutas/Create');
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
            'path' => 'required|string|max:255',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
            'estado' => 'required|in:activo,inactivo',
        ]);
        RutasVotaciones::create($request->all());
        return redirect()->route('rutas.index')
            ->with('success', 'Ruta creada exitosamente.');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $ruta = RutasVotaciones::find($id);
        return Inertia::render('Rutas/Edit', compact('ruta'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */    public function update(Request $request, $id)
    {
        $request->validate([
            'path' => 'required|string|max:255',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
            'estado' => 'required|in:activo,inactivo',
        ]);
        $ruta = RutasVotaciones::find($id);
        $ruta->update($request->all());
        return redirect()->route('rutas.index')
            ->with('success', 'Ruta actualizada exitosamente.');
    }
}
