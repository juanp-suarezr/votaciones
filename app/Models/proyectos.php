<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Proyectos extends Model
{
    use HasFactory;

    protected $table = 'proyectos';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'detalle',
        'descripcion',
        'tipo',
        'subtipo',
        'numero_tarjeton',
        'id_tipo',
        'created_at',
        'updated_at',
        'estado',

    ];

    public function parametroDetalle()
    {
        return $this->belongsTo(ParametrosDetalle::class, 'subtipo', 'id');
    }

    public function tipo_proyecto()
    {
        return $this->belongsTo(Tipo_proyectos::class, 'id_tipo', 'id');
    }

    public function hash_proyectos()
    {
        return $this->hasMany(Hash_proyectos::class, 'id_proyecto', 'id');
    }
}
