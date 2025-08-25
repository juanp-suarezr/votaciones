<?php

namespace App\Observers;

use App\Models\AuditoriaRegistro;
use App\Models\AuditoriaVotos;
use App\Models\Hash_votantes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class ValidacionesObserver
{

    public function updated(Hash_votantes $hash_votantes)
    {
        $this->registrarAuditoria($hash_votantes);
    }


    private function registrarAuditoria($registro)
    {
        AuditoriaRegistro::create([
            'votante_id' => $registro->id,
            'accion' => $registro->estado,
            'id_evento' => $registro->id_evento,
            'usuario_id' => Auth::id(),
            'ip_address' => Request::ip(),
            'user_agent' => Request::header('User-Agent'),
        ]);
    }
}
