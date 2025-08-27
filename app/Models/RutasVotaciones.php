<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RutasVotaciones extends Model
{
    use HasFactory;

    protected $table = 'rutas_votacion';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'path',
        'fecha_inicio',
        'fecha_fin',
        'estado',
        'created_at',
        'updated_at',

    ];


}
