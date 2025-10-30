<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class RebuildFrontend extends Command
{
    protected $signature = 'frontend:build';
    protected $description = 'Ejecuta npm run build automáticamente';

    public function handle()
    {
        $this->info('⚙️ Ejecutando npm run build...');

        $process = new Process(['npm', 'run', 'build']);
        $process->setWorkingDirectory(base_path());
        $process->setTimeout(300); // 5 minutos por si el build tarda

        $process->run(function ($type, $buffer) {
            echo $buffer;
        });

        if ($process->isSuccessful()) {
            $this->info('✅ Frontend reconstruido correctamente.');
        } else {
            $this->error('❌ Error al ejecutar el build: ' . $process->getErrorOutput());
        }
    }
}
