<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Funcionarios_planeacion extends Model
{
    use HasFactory;

    protected $table = 'funcionarios_planeacion';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nombre',
        'identificacion',
        'area',
        'grupo_sanguineo',
        'foto',
        'estado',
        'created_at',
        'updated_at',

    ];

    


}
