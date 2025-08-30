<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Hash_eventos_hijos extends Model
{
    use HasFactory;


    protected $table = 'hash_eventos_hijos';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id_evento_padre',
        'id_evento_hijo',
        'created_at',
        'updated_at',

    ];

    // Relación muchos a uno con Eventos (cada registro de hash pertenece a un evento)
    public function evento_padre()
    {
        return $this->belongsTo(Eventos::class, 'id_evento_padre', 'id');
    }

    // Relación muchos a uno con Informacion_eventos (cada registro de hash pertenece a un votante)
    public function eventos()
    {
        return $this->belongsTo(Eventos::class, 'id_evento_hijo', 'id');
    }

}
