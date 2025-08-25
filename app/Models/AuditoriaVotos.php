<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditoriaVotos extends Model
{
    use HasFactory;

    protected $table = 'auditoria_votos';

    protected $fillable = [
        'voto_id',
        'id_evento',
        'accion',
        'tipo_voto',
        'usuario_id',
        'ip_address',
        'user_agent',
    ];


    public function voto()
    {
        return $this->belongsTo(Votos::class, 'voto_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
