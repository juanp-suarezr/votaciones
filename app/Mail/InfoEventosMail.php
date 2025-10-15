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
     * @return void
     */
    public function __construct(Hash_votantes $votante)
    {
        $this->votante = $votante;

    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {


        return $this
            ->subject('Incio de votaciones Presupuesto Participativo')
            ->view('emails.inicio_eventos')
            ->with([
                'nombre' => $this->votante->votante->nombre,



            ]);
    }
}
