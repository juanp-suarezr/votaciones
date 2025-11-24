<?php

namespace App\Console\Commands;

use App\Mail\AnomaliasMail;
use App\Mail\CertificadosMail;
use App\Mail\InfoEventosMail;
use App\Mail\ProyectosMail;
use App\Models\Acta_escrutino;
use App\Models\Acta_fin;
use App\Models\Acta_inicio;
use Illuminate\Console\Command;
use App\Models\Eventos;
use App\Models\Hash_votantes;
use App\Models\ParametrosDetalle;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
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
            ->with('hash_proyectos.proyecto')
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

        // Obtener los votantes asociados al evento con id 15 para notificar inicio de votaciones
        $votantes = Hash_votantes::where('id_evento', 15)
            ->with('votante')
            ->where('estado', 'Activo') // Solo los activos
            ->get();

        // Obtener los votantes asociados al evento con id 15 para notificar certificados
        $votantes1 = Hash_votantes::where('id_evento', 15)
            ->with('votante.votos')
            ->get();


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

                //crear acta de escrutinio final por comuna
                $fechaInicio = Carbon::parse($event->fecha_inicio);
                foreach ($comunas_activas as $comuna) {

                    $acta_escrutinio = new Acta_escrutino();
                    $acta_escrutinio->tipo = 'virtual';

                    // ✅ Separar fecha y hora correctamente desde el formato "Y-m-d H:i:s"

                    $acta_escrutinio->fecha_inicio = $fechaInicio->format('Y-m-d');
                    $acta_escrutinio->hora_inicio = $fechaInicio->format('H:i:s');

                    $acta_escrutinio->fecha_fin = $now->format('Y-m-d');
                    $acta_escrutinio->hora_cierre = $now->format('H:i:s');
                    $acta_escrutinio->id_evento = $event->id;
                    $acta_escrutinio->id_jurado = null; // Asigna el ID del jurado si es necesario
                    $acta_escrutinio->comuna = $comuna; // IDs separados por |
                    $acta_escrutinio->puesto_votacion = null; // Asigna el puesto de votación si es necesario
                    $acta_escrutinio->total_votantes = $event->votos->where('subtipo', $comuna)->count(); // Total de votos
                    $acta_escrutinio->total_ciudadanos = $event->padre?->votantes?->where('subtipo', $comuna)->count() ?? 0; // Total de votantes

                    $acta_escrutinio->votos_nulos = 0; // Total de votos nulos
                    $acta_escrutinio->votos_blanco = $event->votos->where('subtipo', $comuna)->where('id_proyecto', 0)->count(); // Total de votos blanco
                    $acta_escrutinio->votos_no_marcados = 0; // Total de votos no marcados
                    $acta_escrutinio->observaciones = 'Acta generada automáticamente al cerrar el evento.'; // Observaciones
                    $acta_escrutinio->imagen = 'virtual'; // Asigna la imagen si es necesario


                    $acta_escrutinio->save();
                }
            }

            $this->info('cerrado');
        }

        //si de eventos update esta el evento de id 15
        if ($eventsToUpdate->contains('id', 15)) {

            $evento_h = $eventsToUpdate->filter(function ($evento) {
                return $evento->hash_proyectos->isNotEmpty();
            });

            $this->info($evento_h);


            # code...
            foreach ($votantes as $votante) {
                if (!in_array($votante->subtipo, $comunas_activas)) {
                    continue; // Si no está, salta al siguiente votante
                }
                if ($votante->votante->email !== null && $votante->votante->email !== '' && $votante->votante->email !== 'NA') {
                    Mail::to($votante->votante->email)->send(new InfoEventosMail($votante, $evento_h));
                    
                }
            }

        }

        //
        if ($eventsToClose->contains('id', 15)) {


            foreach ($votantes1 as $votante) {

                if (!in_array($votante->subtipo, $comunas_activas) || $votante->votante->votos->isEmpty()) {
                    continue; // Si no está, salta al siguiente votante
                }

                if ($votante->votante->email !== null && $votante->votante->email !== '' && $votante->votante->email !== 'NA') {
                    Mail::to($votante->votante->email)->send(new CertificadosMail($votante));
                }
            }
        }

        $this->info('Event statuses updated successfully.');
    }
}
