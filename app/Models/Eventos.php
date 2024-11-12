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
        'fecha_inicio',
        'fecha_fin',
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
