<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Acta_escrutino extends Model
{
    use HasFactory;

    protected $table = 'acta_escrutino';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id_evento',
        'id_jurado',
        'comuna',
        'puestos_votacion',
        'nombre_testigo',
        'identificacion_testigo',
        'contacto_testigo',
        'fecha_inicio',
        'fecha_fin',
        'hora_inicio',
        'hora_cierre',
        'votos_nulos',
        'votos_blancos',
        'votos_no_marcados',
        'total_ciudadanos',
        'observaciones',
        'imagen',
        'created_at',
        'updated_at',

    ];

    public function jurado()
    {
        return $this->hasOne(Delegados::class, 'id', 'id_jurado');
    }

    public function evento()
    {
        return $this->hasOne(Eventos::class, 'id', 'id_evento');
    }


}
