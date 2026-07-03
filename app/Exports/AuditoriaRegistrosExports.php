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
    protected $id_user;
    protected $anio;

    public function __construct($id_evento, $id_user = null, $anio = null)
    {

        $this->id_evento = $id_evento;
        $this->id_user = $id_user;
        $this->anio = $anio;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        try {
            $comunasMap = ParametrosDetalle::where('codParametro', 'com01')
                ->get()
                ->pluck('detalle', 'id');

            $auditoria_registro = AuditoriaRegistro::select('id_evento', 'accion', 'votante_id', 'usuario_id', 'ip_address', 'user_agent', 'created_at')
                ->where('id_evento', $this->id_evento)
                ->when($this->id_user !== null && $this->id_user !== '', function ($query) {
                    $query->where('usuario_id', $this->id_user);
                })
                ->when($this->anio, function ($query) {
                    $query->whereYear('created_at', $this->anio);
                })
                ->with('usuario:id,name', 'hash_votante:id_votante,id', 'hash_votante.votante:id,nombre,identificacion,comuna')
                ->get();

            $auditoria_registro->transform(function ($registro) use ($comunasMap) {
                $comunaId = optional($registro->hash_votante->votante)->comuna;
                $comunaNombre = $comunaId && isset($comunasMap[$comunaId]) ? $comunasMap[$comunaId] : 'N/A';

                return [
                    'usuario.name' => optional($registro->usuario)->name ?? 'N/A',
                    'accion' => $registro->accion,
                    'hash_votante.votante.nombre' => optional($registro->hash_votante->votante)->nombre ?? 'N/A',
                    'hash_votante.votante.identificacion' => optional($registro->hash_votante->votante)->identificacion ?? 'N/A',
                    'hash_votante.votante.comuna' => $comunaNombre,
                    'ip_address' => $registro->ip_address,
                    'user_agent' => $registro->user_agent,
                    'created_at' => $registro->created_at,
                ];
            });

            return $auditoria_registro;

        } catch (Throwable $e) {
            Log::error('Error exportando auditoria_registro', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'id_evento' => $this->id_evento,
            ]);
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
