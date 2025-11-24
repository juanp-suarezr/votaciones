<?php

namespace App\Mail;

use App\Models\Eventos;
use App\Models\Hash_votantes;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProyectosMail extends Mailable
{
    use Queueable, SerializesModels;

    public $votante;
    public $evento;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Hash_votantes $votante, Eventos $evento)
    {
        $this->votante = $votante;
        $this->evento = $evento;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $proyectos = [];

        foreach ($this->evento->hash_proyectos as $hash) {

            if (
                isset($hash->proyecto) &&
                $hash->proyecto->subtipo == $this->votante->subtipo
            ) {
                $proyectos[] = $hash->proyecto;
            }
        }


        return $this
            ->subject('Conozca los proyectos para las ' . $this->evento->nombre)
            ->view('emails.proyectos_list')
            ->with([
                'nombre' => $this->votante->votante->nombre,
                'evento' => $this->evento,
                'proyectos' => $proyectos,


            ]);
    }
}
