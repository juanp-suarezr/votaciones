<?php

namespace App\Console\Commands;

use App\Models\Acta_escrutino;
use App\Models\Acta_fin;
use App\Models\Acta_inicio;
use Illuminate\Console\Command;
use App\Models\Eventos;
use App\Models\ParametrosDetalle;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;


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
            ->with([
                'votos',
                'padre.votantes:id,id_evento'
            ])
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

            if ($event->evento_padre) {
                $this->info('es evento padre');
                continue; // Si es un evento padre, salta a la siguiente iteración
            }

            if (!empty($event->tipos) && Str::contains($event->tipos, 'Presupuesto Participativo')) {

                // Crear un acta_inicio asociado al evento
                $acta_inicio = new Acta_inicio();
                $acta_inicio->modalidad = 'virtual';
                $acta_inicio->fecha_inicio = $now;
                $acta_inicio->id_evento = $event->id;
                $acta_inicio->id_jurado = null; // Asigna el ID del jurado si es necesario
                $acta_inicio->comunas = implode('|', $comunas_activas); // IDs separados por |
                $acta_inicio->puesto_votacion = null; // Asigna el puesto de votación si es necesario
                $acta_inicio->save();
            }

            $this->info('inicio');
        }

        // Actualiza el estado de los eventos encontrados
        foreach ($eventsToClose as $event) {
            $event->estado = 'Cerrado';
            $event->save();

            if ($event->evento_padre) {
                $this->info('es evento padre');
                continue; // Si es un evento padre, salta a la siguiente iteración
            }


            if (!empty($event->tipos) && Str::contains($event->tipos, 'Presupuesto Participativo')) {

                // Crear un acta_fin asociado al evento
                $acta_fin = new Acta_fin();
                $acta_fin->modalidad = 'virtual';
                $acta_fin->fecha_cierre = $now;
                $acta_fin->id_evento = $event->id;
                $acta_fin->id_jurado = null; // Asigna el ID del jurado si es necesario
                $acta_fin->comunas = implode('|', $comunas_activas); // IDs separados por |
                $acta_fin->puesto_votacion = null; // Asigna el puesto de votación si es necesario
                $acta_fin->save();

                //crear acta de escrutinio final
                $acta_escrutinio = new Acta_escrutino();
                $acta_escrutinio->tipo = 'virtual';

                // ✅ Separar fecha y hora correctamente desde el formato "Y-m-d H:i:s"
                $fechaInicio = Carbon::parse($event->fecha_inicio);
                $acta_escrutinio->fecha_inicio = $fechaInicio->format('Y-m-d');
                $acta_escrutinio->hora_inicio = $fechaInicio->format('H:i:s');

                $acta_escrutinio->fecha_fin = $now->format('Y-m-d');
                $acta_escrutinio->hora_cierre = $now->format('H:i:s');
                $acta_escrutinio->id_evento = $event->id;
                $acta_escrutinio->id_jurado = null; // Asigna el ID del jurado si es necesario
                $acta_escrutinio->comuna = implode('|', $comunas_activas); // IDs separados por |
                $acta_escrutinio->puesto_votacion = null; // Asigna el puesto de votación si es necesario
                $acta_escrutinio->total_votantes = $event->votos->count(); // Total de votantes
                $acta_escrutinio->total_ciudadanos = $event->padre?->votantes?->count() ?? 0; // Total de votantes
    
                $acta_escrutinio->votos_nulos = 0; // Total de votos nulos
                $acta_escrutinio->votos_blanco = $event->votos->where('id_proyecto', 0)->count(); // Total de votos blanco
                $acta_escrutinio->votos_no_marcados = 0; // Total de votos no marcados
                $acta_escrutinio->observaciones = 'Acta generada automáticamente al cerrar el evento.'; // Observaciones
                $acta_escrutinio->imagen = 'virtual'; // Asigna la imagen si es necesario


                $acta_escrutinio->save();
            }

            $this->info('cerrado');
        }

        $this->info('Event statuses updated successfully.');
    }
}
