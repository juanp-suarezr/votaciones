<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UsuariosBiometricos extends Model
{
    use HasFactory;

    protected $table = 'usuarios_biometricos';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [

        'user_id',
        'embedding',
        'photo',
        'cedula_front',
        'estado',
        'motivo',
        'edad_estimada',
        'created_at',
        'updated_at',

    ];

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

}
