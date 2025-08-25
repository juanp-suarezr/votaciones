<?php

namespace App\Observers;

use App\Models\Votos;
use App\Models\AuditoriaVotos;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class VotosObserver
{
    public function created(Votos $voto)
    {
        $this->registrarAuditoria('create',  $voto);
    }

    public function updated(Votos $voto)
    {
        $this->registrarAuditoria('update', $voto);
    }

    public function deleted(Votos $voto)
    {
        $this->registrarAuditoria('delete', $voto);
    }

    private function registrarAuditoria($accion, $voto)
    {
        AuditoriaVotos::create([
            'voto_id' => $voto->id,
            'accion' => $accion,
            'id_evento' => $voto->id_eventos,
            'tipo_voto' => $voto->isVirtual ? 'virtual' : 'presencial-tic',
            'usuario_id' => Auth::id(),
            'ip_address' => Request::ip(),
            'user_agent' => Request::header('User-Agent'),
        ]);
    }
}
