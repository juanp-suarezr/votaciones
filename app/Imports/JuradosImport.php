<?php

namespace App\Imports;

use App\Models\Delegados;
use App\Models\Hash_votantes;
use App\Models\User;
use App\Models\Votante;
use App\Models\HashVotante;
use App\Models\Informacion_votantes;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class JuradosImport implements ToCollection, WithHeadingRow
{
    private $numRegistrosInsertados = 0;
    private $numRegistrosActualizados = 0;

    protected $eventId;


    public function __construct($eventId)
    {
        $this->eventId = $eventId;
    }

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
                empty($row[0]) || empty($row[1]) || empty($row[2]) ||
                empty($row[3]) || empty($row[4]) || empty($row[5]) || empty($row[6])
            ) {
                return null;
            }

            // Limitar a las primeras 7 columnas
            $row = array_slice($row, 0, 7);

            DB::transaction(function () use ($row) {
                // Buscar por identificación
                $delegados = Delegados::where('identificacion', $row[1])->first();


                // Si existe, actualiza usuario
                if ($delegados) {

                    $user =  User::where('id', $delegados->id_user)->first();
                    $user->update([
                        'email' => $row[5],
                        'identificacion' => $row[5],
                        'password' => Hash::make($row[6]),
                        'estado' => 'Activo',
                    ]);

                    $delegados->update([
                        'id_evento' => $this->eventId,
                        'puntos_votacion' => intval($row[4] ?? 0),
                        'nombre' => $row[0],
                        'identificacion' => $row[1],
                        'contacto' => $row[2],
                        'cargo' => 'Jurado',
                        'tipo' => 'jurado',
                        'comuna' => intval($row[3] ?? 0),
                        'estado' => 1
                    ]);

                    // Incrementar el contador de registros actualizados correctamente
                    $this->numRegistrosActualizados++;
                } else {
                    // Crear un nuevo usuario
                    $user = User::create([
                        'name' => $row[0],
                        'email' => $row[5],
                        'identificacion' => $row[5],
                        'password' => Hash::make($row[6]),
                        'estado' => 'Activo',
                    ]);

                    // Crear un nuevo votante relacionado al usuario
                    $delegados = Delegados::create([
                        'id_evento' => $this->eventId,
                        'puntos_votacion' => intval($row[4] ?? 0),
                        'id_user' => $user->id,
                        'nombre' => $row[0],
                        'identificacion' => $row[1],
                        'contacto' => $row[2],
                        'cargo' => 'Jurado',
                        'tipo' => 'jurado',
                        'comuna' => intval($row[3] ?? 0),
                        'estado' => 1
                    ]);
                    // Incrementar el contador de registros insertados correctamente
                    $this->numRegistrosInsertados++;
                }
            });

            $index++;
        }
    }

    public function getNumRegistrosInsertados()
    {
        return $this->numRegistrosInsertados;
    }

    public function getNumRegistrosActualizados()
    {
        return $this->numRegistrosActualizados;
    }
}
