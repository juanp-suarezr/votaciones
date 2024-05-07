<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Eventos;
use App\Models\Puntos;
use App\Models\Tipos;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;

class EventosController extends Controller
{
    public function index()
    {
        return Inertia::render(

            'Eventos/Index',

            [
                'eventos' => Eventos::whereNot('nombre', 'Admin')->paginate(5),
            ]
        );
    }

    public function create()
    {
        return Inertia::render(

            'Eventos/Add',

            [
                'tipos' => Tipos::pluck('nombre', 'nombre')->all(),
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
                'fecha_inicio' => $fecha_inicio,
                'fecha_fin' => $fecha_fin,
                'estado' => $request->input('estado'),
            ]
        );
        $eventos->save();


        return Redirect::route('eventos.index')->with('message', 'Registro almacenado.');
    }
}
