<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Votos_fisicos extends Model
{
    use HasFactory;

    protected $table = 'votos_fisicos';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id_acta',
        'id_proyecto',
        'cantidad',

    ];

    public function acta()
    {
        return $this->hasOne(Acta_escrutino::class, 'id', 'id_acta');
    }

    public function proyecto()
    {
        return $this->hasOne(Proyectos::class, 'id', 'id_proyecto');
    }
}
