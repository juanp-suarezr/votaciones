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

    /**
     * Resuelve el ID del evento asignado al usuario mediante la relación nativa con hash_votantes o delegados
     */
    public function getEventoAsignadoId(): ?int
    {
        // 1. Relación nativa mediante Informacion_votantes -> Hash_votantes (id_evento)
        if ($this->votantes) {
            $hv = $this->votantes->hashVotantes()->orderBy('id', 'desc')->first();
            if ($hv && $hv->id_evento) {
                return (int)$hv->id_evento;
            }
        }

        // 2. Registro en tabla delegados por id_user
        if ($this->jurado && $this->jurado->id_evento) {
            return (int)$this->jurado->id_evento;
        }

        // 3. Registro en tabla delegados por identificación del votante/usuario
        $identificacion = $this->identificacion ?? $this->votantes?->identificacion;
        if (!empty($identificacion)) {
            $delegado = Delegados::where('identificacion', $identificacion)
                ->whereNotNull('id_evento')
                ->orderBy('id', 'desc')
                ->first();
            if ($delegado && $delegado->id_evento) {
                return (int)$delegado->id_evento;
            }
        }

        return null;
    }

    /**
     * Retorna todos los IDs de eventos a los que está vinculado el usuario vía hash_votantes
     */
    public function getEventosAsignadosIds(): array
    {
        if ($this->votantes) {
            return $this->votantes->hashVotantes()->pluck('id_evento')->filter()->map(fn($id) => (int)$id)->all();
        }
        return [];
    }

    

}
