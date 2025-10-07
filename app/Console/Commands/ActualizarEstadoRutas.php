<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\RutasVotaciones;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

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

        $rutas = RutasVotaciones::all();
        $contador = 0;

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

            $ruta->save();
            $contador++;
            Log::info("Ruta actualizada: {$ruta->ruta} → estado {$ruta->estado}");
        }

        $this->info("✅ Estados actualizados: {$contador} rutas procesadas correctamente.");
    }
}
