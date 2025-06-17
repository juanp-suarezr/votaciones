<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Parametros extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'parametros';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'cod',
        'detalle',
        'estado',

    ];

    //RELACION USER CON parametros detalle
    public function parametrosDetalle()
    {
        return $this->hasMany(ParametrosDetalle::class, 'codParametro', 'cod');
    }

}
