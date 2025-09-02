<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Eventos;
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
        Log::info("Running event status update at {$now}");

        // Busca los eventos con fecha de inicio pasada y estado pendiente
        $eventsToUpdate = Eventos::where('fecha_inicio', '<=', $now)
            ->where('estado', 'Pendiente')
            ->get();

        $eventsToClose = Eventos::where('fecha_fin', '<=', $now)
            ->where('estado', 'Activo')
            ->get();

        $this->info($eventsToUpdate);
        $this->info($eventsToClose);

        Log::info("Events to update: " . $eventsToUpdate);
        Log::info("Events to close: " . $eventsToClose);

        // Actualiza el estado de los eventos encontrados
        foreach ($eventsToUpdate as $event) {
            $event->estado = 'Activo';
            $event->save();
            $this->info('inicio');
        }

        // Actualiza el estado de los eventos encontrados
        foreach ($eventsToClose as $event) {
            $event->estado = 'Cerrado';
            $event->save();
            $this->info('cerrado');
        }

        $this->info('Event statuses updated successfully.');
    }
}
