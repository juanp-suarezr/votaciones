<?php

namespace App\Http\Controllers;

use App\Events\VotoCreado;
use Illuminate\Support\Facades\Request as RequestFacade;
use App\Http\Controllers\Controller;
use App\Models\Hash_proyectos;
use App\Models\Hash_votantes;
use App\Models\Informacion_votantes;
use App\Models\proyectos;
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

        $votos = Votos::where('id_votante', $infoVotante[0]->id)->where('id_eventos', RequestFacade::input('evento'))->first();

        if ($votos) {
            return redirect()->route('dashboard', [
                'error' => true,
            ])->with('success', 'Recurso almacenado exitosamente');
        };

        if (strpos(RequestFacade::input('tipo_evento'), 'Delegados') !== false) {
            return Inertia::render(

                'Votos/VotosDelegados',

                [
                    'delegados' => Hash_votantes::query()
                        ->when(RequestFacade::input('evento') || RequestFacade::input('subtipo_user'), function ($query) {
                            $evento = RequestFacade::input('evento');
                            $subtipo_user = RequestFacade::input('subtipo_user');
                            $query->where('id_evento', $evento)
                                ->where('subtipo', 'like', $subtipo_user);
                        })->where('candidato', 1)->with('votante')->get(),

                    'proyectos' => Hash_proyectos::query()
                        ->when(RequestFacade::input('evento') || RequestFacade::input('subtipo_user'), function ($query) {
                            $evento = RequestFacade::input('evento');
                            $subtipo_user = RequestFacade::input('subtipo_user');

                            $query->whereHas('proyecto', function ($query) use ($subtipo_user) {
                                $query->where('subtipo', 'like', '%' . $subtipo_user . '%')
                                    ->where('estado', 1);
                            });
                            $query->whereHas('evento', function ($query) use ($evento) {
                                $query->where('evento_padre', 'like', '%' . $evento . '%');
                            });
                        })->with('proyecto')->get(),


                    'votante' => $infoVotante,

                ]

            );
        } else if (strpos(RequestFacade::input('tipo_evento'), 'Proyecto') !== false) {

            return Inertia::render(

                'Votos/Proyectos',

                [

                    'proyectos' => Hash_proyectos::query()
                        ->when(RequestFacade::input('evento') || RequestFacade::input('subtipo_user'), function ($query) {
                            $evento = RequestFacade::input('evento');
                            $subtipo_user = RequestFacade::input('subtipo_user');

                            $query->whereHas('proyecto', function ($query) use ($subtipo_user) {
                                $query->where('subtipo', 'like', '%' . $subtipo_user . '%')
                                    ->where('estado', 1);
                            });
                        })->with('proyecto')->get(),


                    'votante' => $infoVotante,

                ]

            );
        };

        return Inertia::render(

            'Votos/Index',

            [
                'candidatos' => Hash_votantes::query()
                    ->when(RequestFacade::input('evento') || RequestFacade::input('tipo_user'), function ($query) {
                        $evento = RequestFacade::input('evento');
                        $tipo_user = RequestFacade::input('tipo_user');
                        $query->where('id_evento', 'like', '%' . $evento . '%')
                            ->Where('tipo', 'like', '%' . $tipo_user . '%');
                    })->where('candidato', 1)->with('votante')->get(),

                'votante' => $infoVotante,

            ]
        );
    }

    //voto normal y por proyecto solo
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


            if ($request->isProyecto) {
                // Crear el voto
                $Votos = Votos::create([
                    'id_votante' => $request->id_votante,
                    'id_candidato' => 0,
                    'id_proyecto' => $request->id_candidato,
                    'id_eventos' => $request->id_eventos,
                    'tipo' => $request->tipo,
                    'subtipo' => $request->subtipo,
                ]);

                // Asocia la información del usuario
                $Votos->save();
            } else {
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
            }






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

    //voto candidato y proyecto
    public function storeDelegadoProyecto(Request $request)
    {
        try {
            // Validación de los datos
            $request->validate([
                'id_votante' => 'required',
                'id_candidato' => 'required',
                'id_candidato1' => 'required',
                'id_proyecto' => 'required',
                'id_evento_padre' => 'required',
                'id_evento' => 'required',
                'tipo' => 'required',
                'subtipo' => 'required',
            ]);


            // Crear el voto 1 delegado
            $Votos = Votos::create([
                'id_votante' => $request->id_votante,
                'id_candidato' => $request->id_candidato,
                'id_proyecto' => null,
                'id_eventos' => $request->id_evento_padre,
                'tipo' => $request->tipo,
                'subtipo' => $request->subtipo,

            ]);

            // Crear el voto 2 delegado mujer
            $Votos1 = Votos::create([
                'id_votante' => $request->id_votante,
                'id_candidato' => $request->id_candidato1,
                'id_proyecto' => null,
                'id_eventos' => $request->id_evento_padre,
                'tipo' => $request->tipo,
                'subtipo' => $request->subtipo,
            ]);


            // Crear el proyecto
            $Votos2 = Votos::create([
                'id_votante' => $request->id_votante,
                'id_candidato' => 0,
                'id_proyecto' => $request->id_proyecto,
                'id_eventos' => $request->id_evento,
                'tipo' => $request->tipo,
                'subtipo' => $request->subtipo,
            ]);


            // Dispara el evento
            // event(new VotoCreado($Votos));
            // event(new VotoCreado($Votos1));
            // event(new VotoCreado($Votos2));

            // Redirige con mensaje de éxito
            return redirect()->route('dashboard', [
                'tipo_evento' => $request->id_evento,
                'tipo_user' => $request->tipo,

            ])->with('success', 'Recurso almacenado exitosamente');
        } catch (\Exception $e) {
            // Captura cualquier error y maneja la excepción

            // Puedes utilizar Inertia o cualquier otro mecanismo para notificar el error.
            // Capturamos la excepción y devolvemos el mensaje de error a Inertia
            return Redirect::back()->with('error', $e->getMessage());
        }
    }
}
