<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditoriaRegistro extends Model
{
    use HasFactory;

    protected $table = 'auditoria_validaciones';

    protected $fillable = [
        'votante_id',
        'id_evento',
        'accion',
        'usuario_id',
        'ip_address',
        'user_agent',
    ];


    public function hash_votante()
    {
        return $this->hasOne(Hash_votantes::class, 'id', 'votante_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

}
