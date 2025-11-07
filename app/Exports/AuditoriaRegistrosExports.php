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
use Illuminate\Support\Facades\DB;
use Throwable;

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
        try {
            DB::beginTransaction();

            $anio_actual = Carbon::now()->year;

            $auditoria_registro = AuditoriaRegistro::select('id_evento', 'accion', 'votante_id', 'usuario_id', 'ip_address', 'user_agent', 'created_at')
                ->where('id_evento', $this->id_evento)
                ->with('usuario:id,name', 'hash_votante:id_votante,id', 'hash_votante.votante:id,nombre,identificacion,comuna')
                ->get();

            // Transform the collection
            $auditoria_registro->transform(function ($registro) {
                return [
                    'usuario.name' => optional($registro->usuario)->name ?? 'N/A',
                    'accion' => $registro->accion,
                    'hash_votante.votante.nombre' => optional($registro->hash_votante->votante)->nombre ?? 'N/A',
                    'hash_votante.votante.identificacion' => optional($registro->hash_votante->votante)->identificacion ?? 'N/A',
                    'hash_votante.votante.comuna' => $registro->hash_votante->votante->comuna ? ParametrosDetalle::where('id', $registro->hash_votante->votante->comuna)->value('detalle') ?? 'N/A' : 'N/A',
                    'ip_address' => $registro->ip_address,
                    'user_agent' => $registro->user_agent,
                    'created_at' => $registro->created_at,
                ];
            });

            // Log::info('Exportando auditoria_registro: ', $auditoria_registro->toArray());

            DB::commit();
            return $auditoria_registro;

        } catch (Throwable $e) {
            DB::rollBack();
            Log::error('Error exportando auditoria_registro', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'id_evento' => $this->id_evento,
            ]);
            // Devolver colección vacía para que la exportación genere un archivo vacío en lugar de fallar silenciosamente
            return collect([]);
        }
    }

    public function headings(): array
    {
        return [
            'Gestor',
            'Accion',
            'Nombre Votante',
            'Numero Identificación',
            'Comuna',
            'IP Address',
            'User Agent',
            'Fecha de validacion',

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
