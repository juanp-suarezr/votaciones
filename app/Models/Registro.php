<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Registro extends Model
{
    use HasFactory;

    protected $table = 'registro';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nombres',
        'apellidos',
        'edad',
        'tipo_documento',
        'identificacion',
        'direccion',
        'sector',
        'telefono',
        'email',
        'genero',
        'condicion',
        'etnia',
        'nivel_estudio',
        'id_puntos',
    ];

    public function puntos_vive()
    {
        return $this->hasOne(Puntos::class, 'id', 'id_puntos');
    }
}
