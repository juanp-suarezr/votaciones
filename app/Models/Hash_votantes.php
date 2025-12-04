<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Hash_votantes extends Model
{
    use HasFactory;


    protected $table = 'hash_votantes';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id_evento',
        'id_votante',
        'candidato',
        'tipo',
        'subtipo',
        'motivo',
        'validaciones',
        'estado',
        'intentos',
        'fisico_info', // Información adicional para votos físicos
        'created_at',
        'updated_at',

    ];

    // Relación muchos a uno con Eventos (cada registro de hash pertenece a un evento)
    public function evento()
    {
        return $this->belongsTo(Eventos::class, 'id_evento', 'id');
    }

    // Relación muchos a uno con Informacion_votantes (cada registro de hash pertenece a un votante)
    public function votante()
    {
        return $this->belongsTo(Informacion_votantes::class, 'id_votante', 'id');
    }

}
