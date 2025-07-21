<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Delegados extends Model
{
    use HasFactory;

    protected $table = 'delegados';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id_evento',
        'id_user',
        'nombre',
        'cargo',
        'identificacion',
        'contacto',
        'tipo',
        'comuna',
        'punto_votacion',
        'firma',
        'estado',
        'created_at',
        'updated_at',

    ];


}
