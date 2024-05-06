<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Votos extends Model
{
    use HasFactory;

    protected $table = 'votos';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id_votante',
        'id_candidato',
        'id_eventos',
        'tipo',
        'created_at',
        'updated_at',
        'estado',
        
    ];

    public function votante()
    {
        return $this->hasOne(Informacion_votantes::class, 'id', 'id_votante');
    }

    public function candidato()
    {
        return $this->hasOne(Informacion_votantes::class, 'id', 'id_candidato');
    }
}
