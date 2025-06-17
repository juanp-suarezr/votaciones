<?php

namespace App\Mail;


use App\Models\Hash_votantes;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InscriptionApprovedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $votante;

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
            ->subject('Estado de tu registro para votación')
            ->view('emails.inscription_approved')
            ->with([
                'nombre' => $this->votante->votante->nombre,
            ]);
    }
}
