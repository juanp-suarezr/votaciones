<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;

class RebuildFrontend extends Command
{
    protected $signature = 'frontend:build';
    protected $description = 'Ejecuta npm run build automáticamente';

    public function handle()
    {
        $this->info('Ejecutando npm run build...');
        exec('cd ' . base_path() . ' && npm run build');
        $this->info('✅ Frontend reconstruido correctamente.');
    }
}
