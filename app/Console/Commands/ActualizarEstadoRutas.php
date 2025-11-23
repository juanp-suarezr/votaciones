<?php

namespace App\Console\Commands;

use App\Mail\AnomaliasMail;
use App\Mail\ProyectosMail;
use App\Models\Eventos;
use App\Models\Hash_votantes;
use App\Models\ParametrosDetalle;
use Illuminate\Console\Command;
use App\Models\RutasVotaciones;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ActualizarEstadoRutas extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rutas:actualizar-estado';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Actualiza el estado de las rutas según las fechas de inicio y fin.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $ahora = Carbon::now();
        $DiasLater = $ahora->copy()->addDays(3);


        // Busca los eventos con fecha de inicio faltando 3 dias para empezar y estado pendiente
        $eventsToStart = Eventos::whereDate('fecha_inicio', $DiasLater->toDateString())
            ->where('estado', 'Pendiente')
            ->where('aviso_inicio_enviado', 0)
            ->get();


        $comunas_activas = ParametrosDetalle::where('codParametro', 'com01')
            ->where('estado', 1)
            ->pluck('id')
            ->toArray();

        // Obtener los votantes asociados al evento con id 15 para notificar inicio de votaciones
        $votantes = Hash_votantes::where('id_evento', 15)
            ->with('votante')
            ->where('estado', 'Activo') // Solo los activos
            ->get();

        //si de eventos proximos a empezar esta el evento de id 15
        // if ($eventsToStart->contains('id', 15)) {
        //     Log::info("Enviando correos de proyectos para evento 15");

        //     $evento15 = Eventos::find(15);
        //     $evento15->aviso_inicio_enviado = 1;
        //     $evento15->save();

        //     $eventos = Eventos::where('estado', '!=', 'Cerrado')->where('estado', '!=', 'Bloqueado')
        //         ->whereHas('evento_hijo', function ($query) {

        //             $query->where('id_evento_padre', 15);
        //         })
        //         ->with('hash_proyectos.proyecto')
        //         ->get();




        //     foreach ($eventos as $event) {
        //         foreach ($votantes as $votante) {
        //             if (!in_array($votante->subtipo, $comunas_activas)) {
        //                 continue; // Si no está, salta al siguiente votante
        //             }
        //             Log::info("Enviando correo a: " . $votante->votante->email);
        //             if ($votante->votante->email !== null && $votante->votante->email !== '' && $votante->votante->email !== 'NA') {
        //                 Mail::to($votante->votante->email)->send(new ProyectosMail($votante, $event));
        //             }
        //         }
        //     }
        // }


        $rutas = RutasVotaciones::all();
        $contador = 0;

        //enviar correo anomalias
        // $votantes = Hash_votantes::where('id_evento', 15)
        //     ->with('votante')
        //     ->where('estado', 'Activo') // Solo los activos
        //     ->get();

        // foreach ($votantes as $votante) {

        //     if ($votante->votante->email !== null && $votante->votante->email !== '' && $votante->votante->email !== 'NA') {
        //         Mail::to($votante->votante->email)->send(new AnomaliasMail($votante));
        //         $this->info("✅ correo enviado: {$votante->votante->email}");
        //     }
        // }

        foreach ($rutas as $ruta) {
            $estadoAnterior = $ruta->estado;

            // Si tiene fechas configuradas
            if ($ruta->fecha_inicio && $ruta->fecha_fin) {
                if ($ahora->between($ruta->fecha_inicio, $ruta->fecha_fin)) {
                    $ruta->estado = 1; // activa
                } else {
                    $ruta->estado = 0; // bloqueada
                }
            } elseif ($ruta->fecha_inicio && !$ruta->fecha_fin) {
                // Solo tiene fecha de inicio
                $ruta->estado = $ahora->greaterThanOrEqualTo($ruta->fecha_inicio) ? 1 : 0;
            } elseif (!$ruta->fecha_inicio && $ruta->fecha_fin) {
                // Solo tiene fecha de fin
                $ruta->estado = $ahora->lessThanOrEqualTo($ruta->fecha_fin) ? 1 : 0;
            }

            if ($ruta->path === 'register' && $ahora->greaterThanOrEqualTo($ruta->fecha_fin)) {
            }

            $ruta->save();
            $contador++;
            Log::info("Ruta actualizada: {$ruta->ruta} → estado {$ruta->estado}");
        }

        $this->info("✅ Estados actualizados: {$contador} rutas procesadas correctamente.");
    }
}
