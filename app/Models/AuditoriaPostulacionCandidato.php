<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditoriaPostulacionCandidato extends Model
{
    use HasFactory;

    protected $table = 'auditoria_candidatos';

    protected $fillable = [
        'postulacion_id',
        'id_votante',
        'id_evento',
        'usuario_id',
        'accion',
        'motivos_rechazo',
        'observaciones',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'motivos_rechazo' => 'array',
    ];

    public function postulacion()
    {
        return $this->belongsTo(Postulaciones_candidatos::class, 'postulacion_id', 'id');
    }

    public function votante()
    {
        return $this->belongsTo(Informacion_votantes::class, 'id_votante', 'id');
    }

    public function evento()
    {
        return $this->belongsTo(Eventos::class, 'id_evento', 'id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id', 'id');
    }
}

