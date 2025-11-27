<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{

    /*
    - PARAMETRO OBLIGATORIO: {param}
    -Opcional: {param?} o {param=algo}
    - OPCION: {--opcion}
    --OPCION que requiera valor: {--opcion=}
    - OPCION con valor default: {--opcion=valor}
    -Abreviatura: {--0|opcion}
    - Multiple value (array): {param*}
     */


    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // $schedule->command('inspire')->hourly();
        // $schedule->command('sanctum:prune-expired --hours=24')->daily();
        // $schedule->command('events:update-status')->everyMinute()->sendOutputTo(storage_path('logs/events-status.log'));
        $schedule->command('events:update-status')->dailyAt('23:25')->sendOutputTo(storage_path('logs/events-status.log'));
        $schedule->command('password_resets:clean')->daily();
        $schedule->command('rutas:actualizar-estado')->daily();
        
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
