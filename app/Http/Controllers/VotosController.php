<?php

namespace App\Http\Controllers;

use App\Events\VotoCreado;
use Illuminate\Support\Facades\Request as RequestFacade;
use App\Http\Controllers\Controller;
use App\Models\Informacion_votantes;
use App\Models\Votos;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class VotosController extends Controller
{
    public function index()
    {
        $infoVotante = Informacion_votantes::where('id_user', Auth::id())->get();

        $votos = Votos::where('id_votante', $infoVotante[0]->id)->first();

        if ($votos) {
            return redirect()->route('dashboard', [
                'error' => true,
            ])->with('success', 'Recurso almacenado exitosamente');
        };
        return Inertia::render(

            'Votos/Index',

            [
                'candidatos' => Informacion_votantes::query()
                    ->when(RequestFacade::input('tipo_evento') || RequestFacade::input('tipo_user'), function ($query) {
                        $tipo_evento = RequestFacade::input('tipo_evento');
                        $tipo_user = RequestFacade::input('tipo_user');
                        $query->where('id_eventos', 'like', '%' . $tipo_evento . '%')
                            ->Where('tipo', 'like', '%' . $tipo_user . '%');
                    })->where('candidato', 1)->orderBy('nombre', 'asc')->get(),

                'votante' => $infoVotante,

            ]
        );
    }

    public function store(Request $request)
    {

        $request->validate([
            'id_votante' => 'required',
            'id_candidato' => 'required',
            'id_eventos' => 'required',
            'tipo' => 'required',
        ]);


        $Votos = Votos::create([
            'id_votante' => $request->id_votante,
            'id_candidato' => $request->id_candidato,
            'id_eventos' => $request->id_eventos,
            'tipo' => $request->tipo,
        ]);



        // Asocia la información del usuario
        $Votos->save();

        event(new VotoCreado($Votos));

    
        return redirect()->route('votos.store', [
            'tipo_evento' => $request->id_eventos,
            'tipo_user' => $request->tipo
        ])->with('success', 'Recurso almacenado exitosamente');
    }
}
