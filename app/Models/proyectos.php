<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Proyectos extends Model
{
    use HasFactory;

    protected $table = 'proyectos';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'detalle',
        'tipo',
        'subtipo',
        'numero_tarjeton',
        'imagen',
        'created_at',
        'updated_at',
        'estado',

    ];

    public function votantes()
    {
        return $this->hasMany(Hash_votantes::class, 'id_evento', 'id');
    }

    public function votos()
    {
        return $this->hasMany(Votos::class, 'id_eventos', 'id');
    }
}
