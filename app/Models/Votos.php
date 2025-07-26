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
        'id_proyecto',
        'id_eventos',
        'tipo',
        'subtipo',
        'isVirtual',
        'created_at',
        'updated_at',
        'estado',

    ];

    public function votante()
    {
        return $this->hasOne(Informacion_votantes::class, 'id', 'id_votante');
    }

    public function evento()
    {
        return $this->hasOne(Eventos::class, 'id', 'id_eventos');
    }

    public function candidato()
    {
        return $this->hasOne(Informacion_votantes::class, 'id', 'id_candidato');
    }

    public function proyecto()
    {
        return $this->hasOne(Proyectos::class, 'id', 'id_proyecto');
    }
}
