<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'identificacion',
        'estado',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    //RELACION USER CON INFO VOTANTES
    public function votantes()
    {
        return $this->hasOne(Informacion_votantes::class, 'id_user', 'id');
    }

    //RELACION USER CON INFO DELEGADOS
    public function jurado()
    {
        return $this->hasOne(Delegados::class, 'id_user', 'id');
    }

    //RELACION USER CON REG BIOMETRICO
    public function biometrico()
    {
        return $this->hasOne(UsuariosBiometricos::class, 'user_id', 'id');
    }

    //para el restablecimiento contraseña metodo
    public function getEmailForPasswordReset()
{
    // Si es PPT y tiene votante asociado
    if (str_starts_with($this->email, 'ppt') && $this->votante) {
        return $this->votante->email; // correo real
    }

    // Usuario normal
    return $this->email;
}

}
