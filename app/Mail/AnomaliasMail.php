<?php

namespace App\Mail;

use App\Models\Eventos;
use App\Models\Hash_votantes;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class AnomaliasMail extends Mailable
{
    use Queueable, SerializesModels;

    public $votante;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Hash_votantes $votantes)
    {
        $this->votante = $votantes;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {


        return $this
            ->subject('Aclaración sobre el mensaje recibido del Presupuesto Participativo')
            ->view('emails.aviso_alomalia')
            ->with([
                'nombre' => $this->votante->votante->nombre,



            ]);
    }
}
