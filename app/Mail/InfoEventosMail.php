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
        foreach ($this->eventos as $evento) {
            // Filtrar solo eventos activos
            if ($evento->estado !== 'Activo') {
                continue;
            }

            $proyectos = [];

            foreach ($evento->hash_proyectos as $hash) {

                if (
                    isset($hash->proyecto) &&
                    $hash->proyecto->subtipo == $this->votante->subtipo
                ) {
                    $proyectos[] = $hash->proyecto;
                }
            }

            $this->proyectos_por_evento[] = [
                'evento' => $evento->nombre,
                'proyectos' => $proyectos
            ];

            Log::info('InfoEventosMail - Proyectos para evento ' . $evento->nombre . ': ' . count($proyectos));

        }


        return $this
            ->subject('Inicio de votaciones / Proyectos priorizados')
            ->view('emails.inicio_eventos')
            ->with([
                'nombre' => $this->votante->votante->nombre ?? 'Usuario',
                'proyectos_por_evento' => $this->proyectos_por_evento
            ]);
    }
}
