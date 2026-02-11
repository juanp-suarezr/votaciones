<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InformeActualizacionVotantesMail extends Mailable
{
    use Queueable, SerializesModels;

    public $datos;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($datos)
    {
        $this->datos = $datos;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
            ->subject('Informe de Actualización de Información de Votantes')
            ->view('emails.informe_actualizacion_votantes')
            ->with([
                'totalRegistros' => $this->datos['totalRegistros'],
                'actualizados' => $this->datos['actualizados'],
                'noEncontrados' => $this->datos['noEncontrados'],
                'noPerteneceEvento' => $this->datos['noPerteneceEvento'] ?? 0,
                'errores' => $this->datos['errores'],
                'votantesActualizados' => $this->datos['votantesActualizados'],
                'votantesNoActualizados' => $this->datos['votantesNoActualizados'],
                'fechaProceso' => $this->datos['fechaProceso'],
                'nombreEvento' => 'Presupuesto participativo',
            ]);
    }
}
