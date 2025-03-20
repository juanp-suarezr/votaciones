<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParametrosDetalle extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'parametros_detalle';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'codParametro',
        'detalle',
        'estado',

    ];

    //RELACION USER CON parametros
    public function parametros()
    {
        return $this->hasOne(Parametros::class, 'codParametros', 'cod');
    }

}
