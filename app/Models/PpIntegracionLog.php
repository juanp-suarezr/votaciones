<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PpIntegracionLog extends Model
{
    use HasFactory;

    protected $table = 'pp_integracion_logs';

    protected $fillable = [
        'tipo',
        'id_convocatoria_green',
        'id_evento_vota',
        'id_proyecto_local',
        'id_postulacion_green',
        'endpoint',
        'metodo',
        'payload_enviado',
        'respuesta_recibida',
        'http_status',
        'estado',
        'intentos',
        'max_intentos',
        'ultimo_error',
        'proximo_reintento_at',
    ];

    protected $casts = [
        'payload_enviado' => 'array',
        'proximo_reintento_at' => 'datetime',
    ];

    public function evento()
    {
        return $this->belongsTo(Eventos::class, 'id_evento_vota', 'id');
    }

    public function proyecto()
    {
        return $this->belongsTo(Proyectos::class, 'id_proyecto_local', 'id');
    }
}

