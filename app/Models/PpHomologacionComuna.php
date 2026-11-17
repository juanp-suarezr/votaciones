<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PpHomologacionComuna extends Model
{
    use HasFactory;

    protected $table = 'pp_homologacion_comunas';

    protected $fillable = [
        'green_comuna_id',
        'green_nombre',
        'green_tipo',
        'green_grupo',
        'vota_comuna_id',
        'vota_nombre',
        'homologado_manual',
    ];

    protected $casts = [
        'homologado_manual' => 'boolean',
    ];

    public function comunaVota()
    {
        return $this->belongsTo(ParametrosDetalle::class, 'vota_comuna_id', 'id');
    }
}

