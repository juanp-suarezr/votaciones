<?php

namespace App\Imports;

use App\Models\Acta_escrutino;
use App\Models\Delegados;
use App\Models\Eventos;
use App\Models\Hash_votantes;
use App\Models\User;
use App\Models\Votante;
use App\Models\HashVotante;
use App\Models\Informacion_votantes;
use App\Models\ParametrosDetalle;
use App\Models\Votos;
use App\Models\Votos_fisicos;
use App\Models\VotosDuplicados;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class VotantesFisicosImport implements ToCollection, WithHeadingRow
{
    private $numRegistrosInsertados = 0;

    private $numInconsistencias = 0;


    public function __construct() {}

    public function collection($rows)
    {
        $index = 0;

        foreach ($rows as $row) {

            if ($index === 0) {
                $index++;
                continue; // Saltar encabezado manualmente
            }
            $row = array_values($row->toArray());

            // Validar que las 7 primeras columnas tengan datos
            if (
                empty($row[0]) || empty($row[1]) || empty($row[2]) || empty($row[5])
            ) {
                return null;
            }

            // Limitar a las primeras 6 columnas
            $row = array_slice($row, 0, 6);

            try {

                DB::transaction(function () use ($row) {

                    //votante
                    $votante = Informacion_votantes::where('identificacion', $row[2])->first();

                    if (!$votante) {
                        $votante = new Informacion_votantes();
                        $votante->nombre = $row[0];
                        $votante->email = $row[1];
                        $votante->identificacion = $row[2];
                        $votante->nacimiento = $row[3] ?? null;
                        $votante->genero = $row[4] ?? null;
                        $votante->comuna = $row[5];
                        $votante->save();

                        //crear hash_votante
                        $hashVotante = Hash_votantes::create([
                            'id_votante' => $votante->id,
                            'id_evento' => 15,
                            'tipo' => 'Votante',
                            'subtipo' => $row[5],
                            'candidato' => 0, // Asignar 0 para indicar que no es candidato
                            'validaciones' => 'voto fisico',
                            'estado' => 'Activo',
                            'intentos' => 0,
                        ]);
                        $hashVotante->save();
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

                            //crear una parte de voto duplicado
                            $voto_duplicado = new VotosDuplicados();
                            $voto_duplicado->id_votante = $votante->id;
                            $voto_duplicado->id_evento = $evento_hijo->id_evento_hijo;


                            // 1. Obtener todos los votos virtuales y físicos del votante en el evento hijo
                            $votos_virtuales = Votos::where('id_votante', $votante->id)
                                ->where('id_eventos', $evento_hijo->id_evento_hijo)
                                ->get();


                            // 3. Si hay más de uno, elegir uno aleatorio para conservar
                            if ($votos_virtuales->count() > 1) {

                                // Incrementar el contador de incosistencias encontradas
                                $this->numInconsistencias++;

                                $voto_conservar = $votos_virtuales->random();
                                // 4. Eliminar los demás votos
                                $all_votos = $votos_virtuales->where('id', '!=', $voto_conservar->id);
                                foreach ($all_votos as $voto) {
                                    $voto->delete();
                                }

                                //eliminar voto fisico
                                $acta = Acta_escrutino::where('id_evento', $evento_hijo->id_evento_hijo)
                                    ->where('comuna', $row[5])
                                    ->where('tipo', 'fisico')
                                    ->first();

                                    if ($acta) {
                                    $acta->votos_nulos += 1;
                                    $acta->save();

                                    $voto_fisico = Votos_fisicos::where('id_acta', $acta->id)
                                        ->get();



                                    $voto_a_modificar = $voto_fisico->random();
                                    if ($voto_fisico->count() === 0) {
                                        $voto_a_modificar = $voto_fisico->first();
                                    }

                                    if ($voto_a_modificar->cantidad > 0) {
                                        $voto_a_modificar->cantidad -= 1;
                                        $voto_a_modificar->save();
                                    }
                                } else {
                                    Log::error('No se encontró acta física para evento hijo: ' . $evento_hijo->id_evento_hijo . ', comuna: ' . $row[5]);
                                    // Puedes continuar o manejar el error según tu lógica

                                }

                                

                                // 5. Registrar duplicados anulados
                                $voto_duplicado->cantidad_anulada = $all_votos->count() + 1;
                                $voto_duplicado->save();
                            } else if ($votos_virtuales->count() === 1) {

                                //eliminar voto fisico
                                $acta = Acta_escrutino::where('id_evento', $evento_hijo->id_evento_hijo)
                                    ->where('comuna', $row[5])
                                    ->where('tipo', 'fisico')
                                    ->first();

                                if ($acta) {
                                    $acta->votos_nulos += 1;
                                    $acta->save();

                                    $voto_fisico = Votos_fisicos::where('id_acta', $acta->id)
                                        ->get();



                                    $voto_a_modificar = $voto_fisico->random();
                                    if ($voto_fisico->count() === 0) {
                                        $voto_a_modificar = $voto_fisico->first();
                                    }

                                    if ($voto_a_modificar->cantidad > 0) {
                                        $voto_a_modificar->cantidad -= 1;
                                        $voto_a_modificar->save();
                                    }
                                } else {
                                    Log::error('No se encontró acta física para evento hijo: ' . $evento_hijo->id_evento_hijo . ', comuna: ' . $row[5]);
                                    // Puedes continuar o manejar el error según tu lógica

                                }


                                // 5. Registrar duplicados anulados
                                $voto_duplicado->cantidad_anulada = 1;
                                $voto_duplicado->save();
                            } else {
                                // Si no hay votos virtuales, verificar votos físicos
                                $acta = Acta_escrutino::where('id_evento', $evento_hijo->id_evento_hijo)
                                    ->where('comuna', $row[5])
                                    ->where('tipo', 'fisico')
                                    ->first();

                                if ($acta) {
                                    $acta->votos_nulos += 1;
                                    $acta->save();

                                    $voto_fisico = Votos_fisicos::where('id_acta', $acta->id)
                                        ->get();

                                    $voto_a_modificar = $voto_fisico->random();
                                    if ($voto_a_modificar->cantidad > 0) {
                                        $voto_a_modificar->cantidad -= 1;
                                        $voto_a_modificar->save();
                                    }

                                } else {
                                    Log::error('No se encontró acta física para evento hijo: ' . $evento_hijo->id_evento_hijo . ', comuna: ' . $row[5]);
                                    // Puedes continuar o manejar el error según tu lógica

                                }


                                // 5. Registrar duplicados anulados
                                $voto_duplicado->cantidad_anulada = 1;
                                $voto_duplicado->save();
                            }
                        }
                    }


                    // Incrementar el contador de registros insertados correctamente
                    $this->numRegistrosInsertados++;
                });
            } catch (\Exception $e) {
                Log::error('Error en importación de votantes físicos: ' . $e->getMessage());


                continue;
            }

            $index++;
        }
    }

    public function getNumRegistrosInsertados()
    {
        return $this->numRegistrosInsertados;
    }

    public function getNumInconsistencias()
    {
        return $this->numInconsistencias;
    }
}
