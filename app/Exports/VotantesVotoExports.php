<?php

namespace App\Exports;

use App\Models\Empresa;
use App\Models\Votos;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithHeadings;

class VotantesVotoExports implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize
{
    protected $search;
    protected $subtipo;
    protected $id_evento;

    public function __construct($id_evento, $subtipo, $search)
    {
        $this->search = $search;
        $this->subtipo = $subtipo;
        $this->id_evento = $id_evento;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {



        $votantes = Votos::select(
            'id_votante',
            'id_eventos',
            'subtipo',
            'created_at',
            'updated_at',
            'estado',
        )
            ->where('id_eventos', $this->id_evento)
            ->when($this->subtipo, function ($query, $subtipo) {
                $query->where('subtipo',  $subtipo);
            })
            ->whereHas('votante', function ($query) {
                $query->when($this->search, function ($query, $search) {
                    $query->where('nombre', 'like', '%' . $search . '%')
                        ->orWhere('identificacion', 'like', '%' . $search . '%');
                });
            })
            ->with('votante')->with(['votante' => function ($query) {
                $query->select('id', 'nombre', 'tipo_documento', 'identificacion', 'genero', 'created_at'); // agrega aquí solo los campos necesarios
            }])
            ->get();



        // Transform the collection
        $votantes->transform(function ($votante) {

            foreach ($votante->getAttributes() as $key => $value) {
                // Escapar solo los valores que son cadenas de texto (excluyendo fechas y números)
                if (is_string($value) && !strtotime($value) && !is_numeric($value)) {
                    $firstChar = $value[0];
                    if (in_array($firstChar, ['=', '+', '@', '('])) {
                        $votante->$key = "'" . $value;
                    }
                }
            }

            // Asegurar que num_ofertas se coloca en la posición correcta
            return [
                'votante.nombre' => $votante->votante->nombre,
                'votante.tipo_documento' => $votante->votante->tipo_documento,
                'votante.identificacion' => $votante->votante->identificacion,
                'votante.genero' => $votante->votante->genero,
                'votante.created_at' => $votante->votante->created_at,
                'created_at' => $votante->created_at,

            ];
        });

        return $votantes;
    }

    public function headings(): array
    {
        return [
            'Nombre',
            'Tipo de documento',
            'Identificación',
            'Genero',
            'Fecha creación registro',
            'Fecha de voto',

        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 14],
                'borders' => [
                    'outline' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        'color' => ['rgb' => '000000'], // Color de borde negro
                    ],
                ],
            ],
        ];
    }
}
