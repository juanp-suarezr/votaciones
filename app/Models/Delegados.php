<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Delegados extends Model
{
    use HasFactory;

    protected $table = 'delegados';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id_evento',
        'id_user',
        'nombre',
        'cargo',
        'identificacion',
        'contacto',
        'tipo',
        'comuna',
        'puntos_votacion',
        'firma',
        'estado',
        'created_at',
        'updated_at',

    ];

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'id_user');
    }

    public function evento()
    {
        return $this->hasOne(Eventos::class, 'id', 'id_evento');
    }

    public function puntos_votacion_rp()
    {
        return $this->hasOne(ParametrosDetalle::class, 'id', 'puntos_votacion');
    }


}
