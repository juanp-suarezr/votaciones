<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Eventos;
use App\Models\Puntos;
use App\Models\Tipos;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;

class EventosController extends Controller
{

    function __construct()
    {
        $this->middleware('permission:eventos-listar|eventos-crear|eventos-editar|eventos-eliminar', ['only' => ['index', 'store']]);
        $this->middleware('permission:eventos-crear', ['only' => ['create', 'store']]);
        $this->middleware('permission:eventos-editar', ['only' => ['edit', 'update']]);
        $this->middleware('permission:eventos-eliminar', ['only' => ['destroy']]);
    }

    public function index()
    {
        return Inertia::render(

            'Eventos/Index',

            [
                'eventos' => Eventos::whereNot('nombre', 'Admin')->with('evento_padre')->paginate(5),
            ]
        );
    }

    public function create()
    {
        return Inertia::render(

            'Eventos/Add',

            [
                'tipos' => Tipos::pluck('nombre', 'nombre')->all(),
                'eventos' => Eventos::whereNot('nombre', 'Admin')->get(),
            ]
        );
    }

    public function store(Request $request)
    {

        $this->validate($request, [
            'nombre' => 'required|string|max:255',
            'descripcion' => 'required|string|max:300',
            'dependencias' => 'required|string|max:60',
            'fecha_inicio' => 'required',
            'fecha_fin' => 'required',
            'tipos' => 'required'
        ]);

        $fecha_inicio = Carbon::parse($request->input('fecha_inicio'))->format('Y-m-d H:i:s');
        $fecha_fin = Carbon::parse($request->input('fecha_fin'))->format('Y-m-d H:i:s');

        $eventos = Eventos::create(
            [
                'nombre' => $request->input('nombre'),
                'descripcion' => $request->input('descripcion'),
                'dependencias' => $request->input('dependencias'),
                'tipos' => $request->input('tipos'),
                'evento_padre' => $request->input('evento_padre'),
                'fecha_inicio' => $fecha_inicio,
                'fecha_fin' => $fecha_fin,
                'estado' => $request->input('estado'),
            ]
        );
        $eventos->save();

        Cache::forget('eventos_admin');

        return Redirect::route('eventos.index')->with('message', 'Registro almacenado.');
    }

    public function edit(Request $request,$id_user)
    {
        $tipos = Tipos::pluck('nombre', 'nombre')->all();
        $evento = Eventos::findOrFail($id_user);
        $eventos = Eventos::whereNot('nombre', 'Admin')->whereNot('id', $id_user)->get();

        return Inertia::render('Eventos/Edit', compact('tipos', 'evento', 'eventos'));
    }

    public function update(Request $request, $id_ev)
    {

        $this->validate($request, [
            'nombre' => 'required|string|max:255',
            'descripcion' => 'required|string|max:300',
            'dependencias' => 'required|string|max:60',
            'fecha_inicio' => 'required',
            'fecha_fin' => 'required',
            'tipos' => 'required',
            'estado' => 'required'
        ]);

        $eventos = Eventos::find($id_ev);
        $eventos->nombre = $request->nombre;
        $eventos->descripcion = $request->descripcion;
        $eventos->dependencias = $request->dependencias;
        $eventos->fecha_inicio = $request->fecha_inicio;
        $eventos->fecha_fin = $request->fecha_fin;
        $eventos->tipos = $request->tipos;
        $eventos->evento_padre = $request->evento_padre;
        $eventos->estado = $request->estado;
        $eventos->save();


        return Redirect::route('eventos.edit',$eventos->id);
    }

}
