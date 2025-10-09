<?php

namespace App\Imports;

use App\Models\Delegados;
use App\Models\Hash_votantes;
use App\Models\User;
use App\Models\Votante;
use App\Models\HashVotante;
use App\Models\Informacion_votantes;
use App\Models\ParametrosDetalle;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ParametrosDetalleImport implements ToCollection, WithHeadingRow
{
    private $numRegistrosInsertados = 0;

    protected $eventId;


    public function __construct()
    {
        
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
                empty($row[0]) || empty($row[1])
            ) {
                return null;
            }

            // Limitar a las primeras 7 columnas
            $row = array_slice($row, 0, 2);

            DB::transaction(function () use ($row) {

                // Crear un nuevo usuario
                $detalleParametro = ParametrosDetalle::create([
                    'detalle' => $row[0],
                    'codParametro' => $row[1],
                ]);


                // Incrementar el contador de registros insertados correctamente
                $this->numRegistrosInsertados++;
            });

            $index++;
        }
    }

    public function getNumRegistrosInsertados()
    {
        return $this->numRegistrosInsertados;
    }


}
