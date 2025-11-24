<?php

namespace App\Mail;

use App\Models\Eventos;
use App\Models\Hash_votantes;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class InfoEventosMail extends Mailable
{
    use Queueable, SerializesModels;

    public $votante;
    public $eventos;

    /**
     * Create a new message instance.
     *
     * @param  Hash_votantes  $votante
     * @param  mixed          $eventos  (array|Collection)
     */
    public function __construct(Hash_votantes $votante, Eventos $eventos)
    {
        $this->votante = $votante;
        $this->eventos = collect($eventos);
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $proyectos_por_evento = [];

        foreach ($this->eventos as $evento) {

            foreach ($evento->eventos_hijos as $hijoHash) {

                $eventoHijo = $hijoHash->eventos;

                if (!$eventoHijo || $eventoHijo->estado !== 'Activo') {
                    continue;
                }

                $proyectos = [];

                foreach ($eventoHijo->hash_proyectos as $hash) {

                    if (
                        $hash->proyecto &&
                        $this->votante->subtipo &&
                        (string)$hash->proyecto->subtipo === (string)$this->votante->subtipo
                    ) {
                        $proyectos[] = $hash->proyecto;
                    }
                }

                if (!empty($proyectos)) {
                    $proyectos_por_evento[] = [
                        'evento' => $eventoHijo,
                        'proyectos' => $proyectos,
                    ];
                }
            }
        }


        return $this
            ->subject('Inicio de votaciones / Proyectos disponibles')
            ->view('emails.inicio_eventos')
            ->with([
                'nombre' => $this->votante->votante->nombre ?? 'Usuario',
                'proyectos_por_evento' => $proyectos_por_evento,
            ]);
    }
}
