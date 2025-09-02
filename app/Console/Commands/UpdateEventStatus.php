<?php

namespace App\Console\Commands;

use App\Models\Acta_fin;
use App\Models\Acta_inicio;
use Illuminate\Console\Command;
use App\Models\Eventos;
use App\Models\ParametrosDetalle;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class UpdateEventStatus extends Command
{
    protected $signature = 'events:update-status';
    protected $description = 'Update events status based on start date';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $now = Carbon::now();

        // Busca los eventos con fecha de inicio pasada y estado pendiente
        $eventsToUpdate = Eventos::where('fecha_inicio', '<=', $now)
            ->where('estado', 'Pendiente')
            ->get();


        $eventsToClose = Eventos::where('fecha_fin', '<=', $now)
            ->where('estado', 'Activo')
            ->get();

        $this->info($eventsToUpdate);
        $this->info($eventsToClose);


        $comunas_activas = ParametrosDetalle::where('codParametro', 'com01')
                ->where('estado', 1)
                ->pluck('id')
                ->toArray();

        // Actualiza el estado de los eventos encontrados
        foreach ($eventsToUpdate as $event) {
            $event->estado = 'Activo';
            $event->save();
            // Crear un acta_inicio asociado al evento
            $acta_inicio = new Acta_inicio();
            $acta_inicio->modalidad = 'virtual';
            $acta_inicio->fecha_inicio = $now;
            $acta_inicio->id_evento = $event->id;
            $acta_inicio->id_jurado = null; // Asigna el ID del jurado si es necesario
            $acta_inicio->comunas = implode('|', $comunas_activas); // IDs separados por |
            $acta_inicio->puesto_votacion = null; // Asigna el puesto de votación si es necesario
            $acta_inicio->save();

            $this->info('inicio');
        }

        // Actualiza el estado de los eventos encontrados
        foreach ($eventsToClose as $event) {
            $event->estado = 'Cerrado';
            $event->save();

            // Crear un acta_fin asociado al evento
            $acta_fin = new Acta_fin();
            $acta_fin->modalidad = 'virtual';
            $acta_fin->fecha_cierre = $now;
            $acta_fin->id_evento = $event->id;
            $acta_fin->id_jurado = null; // Asigna el ID del jurado si es necesario
            $acta_fin->comunas = implode('|', $comunas_activas); // IDs separados por |
            $acta_fin->puesto_votacion = null; // Asigna el puesto de votación si es necesario
            $acta_fin->save();

            $this->info('cerrado');
        }

        $this->info('Event statuses updated successfully.');
    }
}
