<?php

namespace App\Exports;

use App\Models\AuditoriaRegistro;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Facades\Log;
use Throwable;

class AuditoriaRegistrosExports implements FromCollection, WithHeadings
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

    public function collection()
    {
        try {
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

            return $auditoria_registro->map(function ($registro) {
                return [
                    optional($registro->usuario)->name ?? 'N/A',
                    $registro->accion,
                    optional($registro->hash_votante->votante)->nombre ?? 'N/A',
                    optional($registro->hash_votante->votante)->identificacion ?? 'N/A',
                    optional($registro->hash_votante->votante)->comuna ?? 'N/A',
                    $registro->ip_address,
                    $registro->user_agent,
                    $registro->created_at ? $registro->created_at->format('Y-m-d H:i:s') : 'N/A',
                ];
            });
        } catch (Throwable $e) {
            Log::error('Error exportando auditoria_registro', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'id_evento' => $this->id_evento,
            ]);
            return collect([[]]);
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
}
