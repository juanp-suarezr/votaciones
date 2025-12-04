<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VotosDuplicados extends Model
{
    use HasFactory;

    protected $table = 'votos_duplicados';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id_votante',
        'id_evento',
        'tipo',
        'cantidad',

    ];

    public function votante()
    {
        return $this->hasOne(Informacion_votantes::class, 'id', 'id_votante');
    }

    public function evento()
    {
        return $this->hasOne(Eventos::class, 'id', 'id_evento');
    }


}
