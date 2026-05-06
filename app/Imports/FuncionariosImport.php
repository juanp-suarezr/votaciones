<?php

namespace App\Imports;

use App\Models\Funcionarios_planeacion;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class FuncionariosImport implements ToCollection, WithHeadingRow, WithValidation
{
    private $errors = [];

    private $imported = 0;

    public function collection(Collection $collection)
    {
        foreach ($collection as $key => $row) {
            // Saltar filas vacías
            if (empty(trim($row['nombre'] ?? '')) || empty(trim($row['identificacion'] ?? ''))) {
                Log::info('Fila '.($key + 2).' saltada: nombre o identificación vacíos.');

                continue;
            }

            // Validar datos básicos
            $nombre = trim($row['nombre']);
            $identificacion = trim($row['identificacion']);
            $area = trim($row['area'] ?? '');
            $grupoSanguineo = trim($row['grupo_sanguineo'] ?? '');

            if (strlen($nombre) < 2) {
                $this->errors[] = 'Fila '.($key + 2).': Nombre debe tener al menos 2 caracteres.';

                continue;
            }

            if (strlen($identificacion) > 20) {
                $this->errors[] = 'Fila '.($key + 2).': Identificación demasiado larga.';

                continue;
            }

            if (! empty($area) && strlen($area) > 255) {
                $this->errors[] = 'Fila '.($key + 2).': Área demasiado larga.';

                continue;
            }

            // Verificar si ya existe
            $existing = Funcionarios_planeacion::where('identificacion', $identificacion)->first();
            if ($existing) {
                $this->errors[] = 'Fila '.($key + 2).": Funcionario con identificación {$identificacion} ya existe.";

                continue;
            }

            try {
                Funcionarios_planeacion::create([
                    'nombre' => $nombre,
                    'identificacion' => $identificacion,
                    'area' => $area ?: null,
                    'grupo_sanguineo' => $grupoSanguineo ?: null,
                    'estado' => 1,
                    'foto' => 'NA',
                ]);
                $this->imported++;
            } catch (\Exception $e) {
                $this->errors[] = 'Fila '.($key + 2).': Error al crear funcionario: '.$e->getMessage();
                Log::error('Error creando funcionario en fila '.($key + 2).': '.$e->getMessage());
            }
        }
    }

    public function rules(): array
    {
        return [
            'nombre' => 'required|string|max:200',
            'identificacion' => 'required|string|max:20',
            'area' => 'nullable|string|max:255',
            'grupo_sanguineo' => 'nullable|string|max:10',
        ];
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function getImportedCount()
    {
        return $this->imported;
    }
}
