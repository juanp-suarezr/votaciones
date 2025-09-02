<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Acta_inicio extends Model
{
    use HasFactory;

    protected $table = 'acta_inicio';

    protected $fillable = [
        'modalidad',
        'fecha_inicio',
        'id_evento',
        'id_jurado',
        'comunas',
        'puesto_votacion',
    ];

    protected $casts = [
        'fecha_inicio' => 'datetime',
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
