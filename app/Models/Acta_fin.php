<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Acta_fin extends Model
{
    use HasFactory;

    protected $table = 'acta_fin';

    protected $fillable = [
        'modalidad',
        'fecha_cierre',
        'id_evento',
        'id_jurado',
        'comunas',
        'puesto_votacion',
    ];

    protected $casts = [
        'fecha_cierre' => 'datetime',
    ];

    // Ejemplo de relaciones
    public function evento()
    {
        return $this->belongsTo(Eventos::class, 'id_evento');
    }

    public function jurado()
    {
        return $this->belongsTo(Delegados::class, 'id_jurado');
    }
}
