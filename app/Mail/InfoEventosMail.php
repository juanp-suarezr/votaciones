<?php

namespace App\Mail;

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
    public $proyectos_por_evento = [];

    /**
     * @param Hash_votantes $votante
     * @param mixed $eventos (array|Collection)
     */
    public function __construct(Hash_votantes $votante, $eventos)
    {
        $this->votante = $votante;
        $this->eventos = collect($eventos);
    }

    public function build()
    {
        foreach ($this->eventos as $eventoPadre) {

            foreach ($eventoPadre->eventos_hijos as $hashHijo) {

                $eventoHijo = $hashHijo->eventos;

                if (!$eventoHijo || ($eventoHijo->estado == 'Activo' || $eventoHijo->estado == 'Pendiente')) {
                    continue;
                }

                Log::info("Procesando evento hijo: {$eventoHijo->id}");

                $proyectos = [];

                foreach ($eventoHijo->hash_proyectos as $hash) {

                    if (
                        $hash->proyecto &&
                        $hash->proyecto->subtipo &&
                        $this->votante->subtipo &&
                        (string) $hash->proyecto->subtipo === (string) $this->votante->subtipo
                    ) {
                        $proyectos[$hash->proyecto->id] = $hash->proyecto;
                        // ← Usa ID como key para evitar duplicados
                    }
                }

                if (!empty($proyectos)) {
                    $this->proyectos_por_evento[] = [
                        'evento'     => $eventoHijo,
                        'proyectos'  => array_values($proyectos)
                    ];
                }
            }
        }

        Log::info('Proyectos por evento enviados al correo: ', $this->proyectos_por_evento);

        return $this
            ->subject('Inicio de votaciones / Proyectos priorizados')
            ->view('emails.inicio_eventos')
            ->with([
                'nombre' => $this->votante->votante->nombre ?? 'Usuario',
                'proyectos_por_evento' => $this->proyectos_por_evento
            ]);
    }
}
