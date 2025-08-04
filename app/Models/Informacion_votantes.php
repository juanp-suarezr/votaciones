<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Informacion_votantes extends Model
{
    use HasFactory;

    protected $table = 'votantes';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nombre',
        'id_user',
        'id_jurado',
        'identificacion',
        'tipo_documento',
        // 'fecha_expedicion',
        // 'lugar_expedicion',
        'nacimiento',
        'genero',
        'etnia',
        'condicion',
        'direccion',
        'celular',
        'comuna',
        'barrio',
        'created_at',
        'updated_at',
        'imagen',

    ];

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'id_user');
    }

    public function hashVotantes()
    {
        return $this->hasMany(Hash_votantes::class, 'id_votante', 'id');
    }

    public function jurado()
    {
        return $this->hasOne(Delegados::class, 'id', 'id_jurado');
    }

    public function votos()
    {
        return $this->hasMany(Votos::class, 'id_votante', 'id');
    }
}
