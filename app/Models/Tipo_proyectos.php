<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tipo_proyectos extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'tipo_proyectos';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nombre',
        'imagen',

    ];

    //RELACION USER CON parametros detalle
    public function tipo_proyecto()
    {
        return $this->hasMany(Proyectos::class, 'id_tipo', 'id');
    }

}
