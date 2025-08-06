<?php

namespace App\Exports;

use App\Models\Empresa;
use App\Models\ParametrosDetalle;
use App\Models\Votos;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Events\AfterSheet;

class JuradosExports implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize, WithEvents
{

    protected $id_evento;

    public function __construct($id_evento)
    {

        $this->id_evento = $id_evento;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {

        return new Collection([
            [
                'Nombre' => 'Jurado 1',
                'identificacion' => '1000000',
                'Numero de contacto' => '342383',
                'id_comuna' => 1,
                'ParametroDetalle' => optional(ParametrosDetalle::where('estado', 1)
                    ->where('codParametro', 'pt_v')
                    ->first())->id,
                'email' => 'jurado1@gmail.com',
                'Contraseña' => '12345678',
            ],
        ]);
    }

    public function headings(): array
    {
        return [
            'Nombre',
            'Identificación',
            'Numero de contacto',
            'id_comuna',
            'id_puesto_votación',
            'Email',
            'Contraseña',

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

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Proteger la hoja
                $sheet->getProtection()->setSheet(true);

                // Desbloquear todas las celdas
                $sheet->getStyle('A2:G2000')->getProtection()->setLocked(\PhpOffice\PhpSpreadsheet\Style\Protection::PROTECTION_UNPROTECTED);


                // Agregar una fila adicional con una nota explicativa
                $sheet->insertNewRowBefore(1, 1);
                $sheet->setCellValue('A1', 'Nota: en la fila de id_comuna debe poner el numero que está en la parte derecha del nombre de cada comuna ubicado en el recuadro amarillo. igualmente con id_punto_votacion y recuadro azul');
                $sheet->mergeCells('A1:I1');

                $sheet->getStyle('A1:I1')->applyFromArray([
                    'font' => ['italic' => true, 'color' => ['rgb' => 'FF0000']],
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'FFFFCC'], // Color de fondo amarillo claro
                    ],
                ]);

                // Bloquear la celda con la nota explicativa
                $sheet->getStyle('A1:I1')->getProtection()->setLocked(\PhpOffice\PhpSpreadsheet\Style\Protection::PROTECTION_PROTECTED);


                // Obtener los puntos
                $puntos = ParametrosDetalle::where('estado', 1)
                    ->where('codParametro', 'pt_v')
                    ->get();
                // Obtener comuna
                $comunas = ParametrosDetalle::where('estado', 1)
                    ->where('codParametro', 'com01')
                    ->get();

                // Agregar título y subtítulo Comunas
                $sheet->setCellValue('H4', 'Comuna');
                $sheet->setCellValue('I4', 'ID');
                $sheet->getStyle('H4:I4')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 14],
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'FFFF00'], // Color de fondo amarillo
                    ],
                ]);

                // Agregar título y subtítulo puestos votacion
                $sheet->setCellValue('J4', 'Puestos votación');
                $sheet->setCellValue('K4', 'ID');
                $sheet->getStyle('J4:K4')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '004884'], // Color de fondo amarillo
                    ],
                ]);



                // Ajustar el ancho de las columnas para que el texto se ajuste
                $sheet->getColumnDimension('H')->setAutoSize(true);
                $sheet->getColumnDimension('I')->setAutoSize(true);
                $sheet->getColumnDimension('J')->setAutoSize(true);
                $sheet->getColumnDimension('K')->setAutoSize(true);

                // Agregar datos de los puntos
                $row = 5;
                $comunaIds = [];
                foreach ($comunas as $comuna) {
                    $sheet->setCellValue('H' . $row, $comuna->detalle);
                    $sheet->setCellValue('I' . $row, $comuna->id);
                    $comunaIds[] = $comuna->id;
                    $row++;
                }

                // Bloquear la celda con la nota explicativa
                $sheet->getStyle('H4:I9')->getProtection()->setLocked(\PhpOffice\PhpSpreadsheet\Style\Protection::PROTECTION_PROTECTED);


                // Aplicar estilo a las celdas de los puntos
                $sheet->getStyle('H5:I' . ($row - 1))->applyFromArray([
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'FFFF00'], // Color de fondo amarillo
                    ],
                ]);

                // Agregar validación de datos para la columna
                $comunaIdsString = implode(',', $comunaIds);
                for ($i = 3; $i <= 3000; $i++) { // Ajustar el índice de fila para la validación
                    $validation = $sheet->getCell('D' . $i)->getDataValidation();
                    $validation->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
                    $validation->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_STOP);
                    $validation->setAllowBlank(true);
                    $validation->setShowInputMessage(true);
                    $validation->setShowErrorMessage(true);
                    $validation->setErrorTitle('Error de entrada');
                    $validation->setError('Solo se permiten los IDs de las comunas.');
                    $validation->setPromptTitle('Entrada de ID');
                    $validation->setPrompt('Por favor, ingrese un ID de comuna válido.');
                    $validation->setFormula1('"' . $comunaIdsString . '"');
                }

                // Agregar datos de los puntos
                $row1 = 5;
                $puntoIds = [];
                foreach ($puntos as $punto) {
                    $sheet->setCellValue('J' . $row1, $punto->detalle);
                    $sheet->setCellValue('K' . $row1, $punto->id);
                    $puntoIds[] = $punto->id;
                    $row1++;
                }

                // Bloquear la celda con la nota explicativa
                $sheet->getStyle('J4:K9')->getProtection()->setLocked(\PhpOffice\PhpSpreadsheet\Style\Protection::PROTECTION_PROTECTED);


                // Aplicar estilo a las celdas de los puntos
                $sheet->getStyle('J5:K' . ($row - 1))->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '004884'], // Color de fondo azul
                    ],
                ]);

                // Agregar validación de datos para la columna id_puntos
                $puntoIdsString = implode(',', $puntoIds);
                for ($i = 3; $i <= 3000; $i++) { // Ajustar el índice de fila para la validación
                    $validation = $sheet->getCell('E' . $i)->getDataValidation();
                    $validation->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
                    $validation->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_STOP);
                    $validation->setAllowBlank(true);
                    $validation->setShowInputMessage(true);
                    $validation->setShowErrorMessage(true);
                    $validation->setErrorTitle('Error de entrada');
                    $validation->setError('Solo se permiten los IDs de los puntos.');
                    $validation->setPromptTitle('Entrada de ID');
                    $validation->setPrompt('Por favor, ingrese un ID de punto válido.');
                    $validation->setFormula1('"' . $puntoIdsString . '"');
                }
            },
        ];
    }
}
