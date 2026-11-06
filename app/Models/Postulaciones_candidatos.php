<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Postulaciones_candidatos extends Model
{
    use HasFactory;

    protected $table = 'postulaciones_candidatos';

    protected $fillable = [
        'id_votante',
        'id_evento',
        'identificacion',
        'tipo_documento',
        'nombre',
        'email',
        'celular',
        'tipo_planta',
        'fecha_postulacion',
        'cumple_requisitos',
        'archivo_procuraduria',
        'archivo_cedula',
        'foto',
        'estado',
        'observaciones',
        'radicado',
    ];

    public function votante()
    {
        return $this->belongsTo(Informacion_votantes::class, 'id_votante', 'id');
    }

    public function evento()
    {
        return $this->belongsTo(Eventos::class, 'id_evento', 'id');
    }
}

