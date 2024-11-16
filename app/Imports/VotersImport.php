<?php

namespace App\Imports;

use App\Models\Hash_votantes;
use App\Models\User;
use App\Models\Votante;
use App\Models\HashVotante;
use App\Models\Informacion_votantes;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class VotersImport implements ToCollection, WithHeadingRow
{
    private $numRegistrosInsertados = 0;
    private $numRegistrosActualizados = 0;

    protected $eventId;
    protected $tipos;

    public function __construct($eventId, $tipos)
    {
        $this->eventId = $eventId;
        $this->tipos = $tipos;
    }

    public function collection($rows)
    {
        foreach ($rows as $row) {
            DB::transaction(function () use ($row) {
                // Buscar por identificación
                $votante = Informacion_votantes::where('identificacion', $row['identificacion'])->first();

                // Si existe, actualiza usuario
                if ($votante) {
                    $user =  User::where('id', $votante->id_user)->first();
                    $user->update([
                        'email' => $row['email'],
                        'password' => Hash::make($row['password']),
                        'estado' => 'Activo',
                    ]);
                    // Incrementar el contador de registros actualizados correctamente
                    $this->numRegistrosActualizados++;
                } else {
                    // Crear un nuevo usuario
                    $user = User::create([
                        'name' => $row['name'],
                        'email' => $row['email'],
                        'password' => Hash::make($row['password']),
                        'estado' => 'Activo',
                    ]);

                    // Crear un nuevo votante relacionado al usuario
                    $votante = Informacion_votantes::create([
                        'id_user' => $user->id,
                        'nombre' => $row['name'],
                        'identificacion' => $row['identificacion'],
                    ]);
                    // Incrementar el contador de registros insertados correctamente
                    $this->numRegistrosInsertados++;
                }

                // Relacionar votante con el evento en hash_votantes
                Hash_votantes::firstOrCreate([
                    'id_evento' => $this->eventId,
                    'id_votante' => $votante->id,
                    'tipo' => $this->tipos,
                ]);
            });
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
