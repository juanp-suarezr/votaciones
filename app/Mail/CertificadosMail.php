<?php

namespace App\Mail;

use App\Models\Eventos;
use App\Models\Hash_votantes;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CertificadosMail extends Mailable
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
            ->subject('Certificados Presupuesto Participativo')
            ->view('emails.certificados')
            ->with([
                'nombre' => $this->votante->votante->nombre,



            ]);
    }
}
