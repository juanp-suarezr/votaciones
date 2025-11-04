<?php

namespace App\Exports;

use App\Models\AuditoriaRegistro;
use App\Models\Empresa;
use App\Models\Parametros;
use App\Models\ParametrosDetalle;
use App\Models\Votos;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithHeadings;

class AuditoriaRegistrosExports implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize
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

        $anio_actual = Carbon::now()->year;


        $auditoria_registro = AuditoriaRegistro::select('id_evento', 'accion', 'votante_id', 'usuario_id', 'ip_address', 'user_agent', 'created_at')
        ->where('id_evento', $this->id_evento)
        ->with('usuario:id,name', 'hash_votante:id_votante,id', 'hash_votante.votante:id,nombre,identificacion,comuna')
            ->orderBy('created_at', 'desc')
            ->paginate(5)
            ->withQueryString(); // Mantener los parámetros en la URL

        // Transform the collection
        $auditoria_registro->transform(function ($registro) {


            // Asegurar que num_ofertas se coloca en la posición correcta
            return [
                'usuario.name' => $registro->usuario->name,
                'accion' => $registro->accion,
                'hash_votante.votante.nombre' => $registro->hash_votante->votante->identificacion,
                'hash_votante.votante.identificacion' => $registro->hash_votante->votante->identificacion,
                'hash_votante.votante.comuna' => ParametrosDetalle::where('id', $registro->hash_votante->votante->comuna)->value('detalle') ?? 'N/A',
                'ip_address' => $registro->ip_address,
                'user_agent' => $registro->user_agent,
                'created_at' => $registro->created_at,

            ];
        });
        Log::info('registros', ['registros' => $auditoria_registro]);
        return $auditoria_registro;
    }

    public function headings(): array
    {
        return [
            'Gestor',
            'Acción',
            'Nombre Votante',
            'Numero Identificación',
            'Comuna',
            'IP Address',
            'User Agent',
            'Fecha de validación',

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
