<?php

namespace App\Http\Controllers;

use App\Events\VotoCreado;
use Illuminate\Support\Facades\Request as RequestFacade;
use App\Http\Controllers\Controller;
use App\Models\Hash_votantes;
use App\Models\Informacion_votantes;
use App\Models\Votos;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;

class VotosController extends Controller
{
    public function index()
    {
        $infoVotante = Informacion_votantes::where('id_user', Auth::id())->get();

        $votos = Votos::where('id_votante', $infoVotante[0]->id)->where('id_eventos', RequestFacade::input('tipo_evento'))->first();

        if ($votos) {
            return redirect()->route('dashboard', [
                'error' => true,
            ])->with('success', 'Recurso almacenado exitosamente');
        };
        return Inertia::render(

            'Votos/Index',

            [
                'candidatos' => Hash_votantes::query()
                    ->when(RequestFacade::input('tipo_evento') || RequestFacade::input('tipo_user'), function ($query) {
                        $tipo_evento = RequestFacade::input('tipo_evento');
                        $tipo_user = RequestFacade::input('tipo_user');
                        $query->where('id_evento', 'like', '%' . $tipo_evento . '%')
                            ->Where('tipo', 'like', '%' . $tipo_user . '%');
                    })->where('candidato', 1)->with('votante')->get(),

                'votante' => $infoVotante,

            ]
        );
    }

    public function store(Request $request)
    {
        try {
            // Validación de los datos
            $request->validate([
                'id_votante' => 'required',
                'id_candidato' => 'required',
                'id_eventos' => 'required',
                'tipo' => 'required',
            ]);

            // Crear el voto
            $Votos = Votos::create([
                'id_votante' => $request->id_votante,
                'id_candidato' => $request->id_candidato,
                'id_eventos' => $request->id_eventos,
                'tipo' => $request->tipo,
            ]);

            // Asocia la información del usuario
            $Votos->save();

            // Dispara el evento
            event(new VotoCreado($Votos));

            // Redirige con mensaje de éxito
            return redirect()->route('votos.store', [
                'tipo_evento' => $request->id_eventos,
                'tipo_user' => $request->tipo
            ])->with('success', 'Recurso almacenado exitosamente');
        } catch (\Exception $e) {
            // Captura cualquier error y maneja la excepción

            // Puedes utilizar Inertia o cualquier otro mecanismo para notificar el error.
            // Capturamos la excepción y devolvemos el mensaje de error a Inertia
            return Redirect::back()->with('error', $e->getMessage());
        }
    }
}
