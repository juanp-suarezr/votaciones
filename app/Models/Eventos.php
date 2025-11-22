<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Eventos extends Model
{
    use HasFactory;

    protected $table = 'eventos';

    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nombre',
        'descripcion',
        'dependencias',
        'tipos',
        'evento_padre',
        'fecha_inicio',
        'fecha_fin',
        'estado',
        'aviso_inicio_enviado',

    ];

    public function votantes()
    {
        return $this->hasMany(Hash_votantes::class, 'id_evento', 'id');
    }

    public function votos()
    {
        return $this->hasMany(Votos::class, 'id_eventos', 'id');
    }

    public function acta_escrutinio()
    {
        return $this->hasMany(Acta_escrutino::class, 'id_evento', 'id');
    }

    // Relación para traerme el evento padre directamente
    public function padre()
    {
        return $this->belongsTo(Eventos::class, 'evento_padre', 'id');
    }


    // Relación muchos a uno con Hash_eventos_hijos (para traerse el evento padre del hijo)
    public function evento_hijo()
    {
        return $this->hasMany(Hash_eventos_hijos::class, 'id_evento_hijo', 'id');
    }

    // Relación uno a muchos con Hash_eventos_hijos (para traerse los eventos hijos del padre)
    public function eventos_hijos()
    {
        return $this->hasMany(Hash_eventos_hijos::class, 'id_evento_padre', 'id');
    }

    //proyectos
    public function hash_proyectos()
    {
        return $this->hasMany(Hash_proyectos::class, 'id_evento', 'id');
    }
}
