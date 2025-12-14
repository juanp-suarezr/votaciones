<?php

namespace App\Http\Controllers;

use App\Models\Eventos;
use App\Models\Hash_proyectos;
use App\Models\Hash_votantes;
use App\Models\Informacion_votantes;
use App\Models\ParametrosDetalle;
use App\Models\Votos;
use App\Models\Votos_fisicos;
use App\Models\VotosDuplicados;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class DuplicidadJuradoController extends Controller
{
    public function index($id)
    {
        $comuna = ParametrosDetalle::where('id', $id)
            ->where('codParametro', 'com01')
            ->where('estado', 1)
            ->firstOrFail();

        return Inertia::render('Duplicidad/Index', [
            'comuna' => $comuna,
        ]);
    }

    public function validar(Request $request, $id)
    {
        $request->validate([
            'numero_identificacion' => 'required|string',
        ]);

        $numero_identificacion = $request->numero_identificacion;

        // Buscar votante
        $votante = Informacion_votantes::where('identificacion', $numero_identificacion)
            ->with('hashVotantes')
            ->where('comuna', '!=', '0')
            ->first();

        $id_votante = $votante ? $votante->id : null;

        $votante_Activo = Informacion_votantes::where('identificacion', $numero_identificacion)
            ->whereHas('hashVotantes', function ($query) {
                $query->where('estado', 'Activo');
            })
            ->where('comuna', '!=', '0')
            ->first();


        //buscar si el votante tiene hash no activo (bloqueado, pendiente, rechazado)
        $votante_no_activo = Hash_votantes::where('id_votante', $id_votante)
            ->where('estado', '!=', 'Activo')
            ->where('fisico_info', '!=', 'ok')
            ->first();
        //buscar si el votante tiene hash activo con voto fisico ok
        $votante_activo_voto_fisico = Hash_votantes::where('id_votante', $id_votante)
            ->where('estado', 'Activo')
            ->where('fisico_info', 'ok')
            ->first();

        if (!$votante) {

            //crear votante
            $votante_create = Informacion_votantes::create([
                'identificacion' => $numero_identificacion,
                'comuna' => $id,
            ]);
            $votante_create->save();

            Log::info('Votante físico creado: ' . $votante_create->id);

            //crear hash_votante
            $hashVotante = Hash_votantes::create([
                'id_votante' => $votante_create->id,
                'id_evento' => 15,
                'tipo' => 'Votante',
                'subtipo' => $id,
                'candidato' => 0, // Asignar 0 para indicar que no es candidato
                'validaciones' => 'voto fisico',
                'estado' => 'Activo',
                'fisico_info' => 'ok',
                'intentos' => 0,
            ]);
            $hashVotante->save();

            return response()->json(['valid' => true, 'message' => 'Votante registrado correctamente.']);
        } else {


            //buscar si ya voto por evento
            $eventos = Eventos::where('id', 15)
                ->with('eventos_hijos.eventos')
                ->first();



            $eventos_hijos = $eventos->eventos_hijos;

            foreach ($eventos_hijos as $evento_hijo) {

                // if($evento_hijo->eventos->estado !== 'Activo'){
                //     continue;
                // }

                // Verificar si el evento tiene proyectos en la comuna actual
                $tiene_proyectos_en_comuna = Hash_proyectos::where('id_evento', $evento_hijo->id_evento_hijo)
                    ->whereHas('proyecto', function ($query) use ($id) {
                        $query->where('subtipo', $id);
                    })
                    ->exists();

                if (!$tiene_proyectos_en_comuna) {
                    continue;
                }

                //crear una parte de voto duplicado
                $voto_duplicado = new VotosDuplicados();
                $voto_duplicado->id_votante = $votante->id;
                $voto_duplicado->id_evento = $evento_hijo->id_evento_hijo;
                $voto_duplicado->comuna = $id;
                $voto_duplicado->procesado = false;


                // 1. Obtener todos los votos virtuales y físicos del votante en el evento hijo
                $votos_virtuales = Votos::where('id_votante', $votante->id)
                    ->where('id_eventos', $evento_hijo->id_evento_hijo)
                    ->get();


                // 3. Si hay más de uno, elegir uno aleatorio para conservar
                if ($votos_virtuales->count() > 1) {


                    $voto_conservar = $votos_virtuales->random();
                    // 4. Eliminar los demás votos
                    $all_votos = $votos_virtuales->where('id', '!=', $voto_conservar->id);
                    foreach ($all_votos as $voto) {
                        $voto->delete();
                    }


                    // 5. Registrar duplicados anulados
                    $voto_duplicado->cantidad_anulada = $all_votos->count() + 1;
                    $voto_duplicado->tipo = 'votos fisicos-virtuales duplicados';
                    $voto_duplicado->save();
                } else if ($votos_virtuales->count() === 1) {


                    // 5. Registrar duplicados anulados
                    $voto_duplicado->cantidad_anulada = 1;
                    $voto_duplicado->tipo = 'votos fisico y uno virtual duplicados';
                    $voto_duplicado->save();
                } else {


                    // votante fisico y virtual no ha votado
                    if (!$votante_activo_voto_fisico && $votante_Activo) {
                        $votante_activo = Hash_votantes::where('id_votante', $votante->id)
                            ->where('estado', 'Activo')
                            ->first();
                        $votante_activo->fisico_info = 'ok';
                        $votante_activo->save();
                        Log::info('Votante con voto físico registrado correctamente: ' . $votante->identificacion);
                        return response()->json(['valid' => true, 'message' => 'Votante registrado correctamente.']);
                        return;
                    }

                    //actualizar votante no activo
                    if ($votante_no_activo) {
                        //actualizar hash_votante
                        $votante_no_activo->fisico_info = 'ok';
                        $votante_no_activo->save();
                        return response()->json(['valid' => true, 'message' => 'Votante actualizado correctamente.']);
                        return;
                    }


                    // 5. Registrar duplicados anulados
                    $voto_duplicado->cantidad_anulada = 1;
                    $voto_duplicado->tipo = 'votos fisico duplicado';
                    $voto_duplicado->save();
                }
            }

            return response()->json(['valid' => true, 'message' => 'Duplicados procesados.']);
        }
    }
}
