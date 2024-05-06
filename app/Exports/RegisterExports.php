<?php

namespace App\Exports;

use App\Models\Registro;
use DB;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithHeadings;

class RegisterExports implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize
{

    protected $startDate;
    protected $endDate;

    public function __construct($startDate, $endDate)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    /**
     * @return \Illuminate\Support\Collection
     */

    public function collection()
    {
        $users = Registro::whereHas('puntos_vive', function ($query) {
            $query->where('id_user', Auth::id()); // Filtrar por el ID del usuario autenticado
        })->when($this->startDate || $this->endDate, function ($query) {
            $fecha_init = $this->startDate;
            $fecha_end = $this->endDate;

            if($fecha_end == null) {
                $fecha_end = date('Y-m-d');
            }

            $query->whereBetween('created_at', [$fecha_init, $fecha_end]);
        })
            ->with('puntos_vive')
            ->select(
                'nombres',
                'apellidos',
                'edad',
                'tipo_documento',
                'identificacion',
                'direccion',
                'sector',
                'telefono',
                'email',
                'genero',
                'condicion',
                'etnia',
                'nivel_estudio',
                'id_puntos', // Necesario para la relación
                'created_at'
            )
            ->latest('created_at')
            ->get();

            if ($users->isEmpty()) {
                $users = Registro::when($this->startDate || $this->endDate, function ($query) {
                    $fecha_init = $this->startDate;
                    $fecha_end = $this->endDate;
        
                    if($fecha_end == null) {
                        $fecha_end = date('Y-m-d');
                    }
        
                    $query->whereBetween('created_at', [$fecha_init, $fecha_end]);
                })->with('puntos_vive')
                    ->select(
                        'nombres',
                        'apellidos',
                        'edad',
                        'tipo_documento',
                        'identificacion',
                        'direccion',
                        'sector',
                        'telefono',
                        'email',
                        'genero',
                        'condicion',
                        'etnia',
                        'nivel_estudio',
                        'id_puntos', // Necesario para la relación
                        'created_at'
                    )
                    ->latest('created_at')
                    ->get();
            }

        // Reemplazar el ID de puntos con el nombre del punto
        $users->transform(function ($user) {
            $user->id_puntos = $user->puntos_vive->nombre ?? ''; // Asignar el nombre del punto o cadena vacía si no hay punto
            // $user->created_at = \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel( $user->created_at );
            unset($user->puntos_vive); // Eliminar la relación ya que no la necesitamos en la exportación
            return $user;
        });

        

        return $users;
    }

    public function headings(): array
    {
        // return [
        //     'nombres',
        //     'apellidos',
        //     'edad',
        //     'tipo_documento',
        //     'identificacion',
        //     'direccion',
        //     'sector',
        //     'telefono',
        //     'email',
        //     'genero',
        //     'condicion',
        //     'etnia',
        //     'nivel_estudio',
        //     'punto',
        //     'fecha_registro'
        // ];
        return array_keys($this->collection()->first()->toArray());
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
