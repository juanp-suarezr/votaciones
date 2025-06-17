<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Hash_proyectos extends Model
{
    use HasFactory;


    protected $table = 'hash_proyectos';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id_evento',
        'id_proyecto',
        'created_at',
        'updated_at',

    ];

    // Relación muchos a uno con Eventos (cada registro de hash pertenece a un evento)
    public function evento()
    {
        return $this->belongsTo(Eventos::class, 'id_evento', 'id');
    }

    // Relación muchos a uno con Informacion_proyectos (cada registro de hash pertenece a un votante)
    public function proyecto()
    {
        return $this->belongsTo(Proyectos::class, 'id_proyecto', 'id');
    }

}
