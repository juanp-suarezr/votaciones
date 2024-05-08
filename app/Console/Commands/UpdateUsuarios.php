<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Eventos;
use App\Models\Informacion_votantes;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

class UpdateUsuarios extends Command
{
    protected $signature = 'events:update-usuarios';
    protected $description = 'Update events status based on start date';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {

        $usuarios = User::with('votantes')->whereNot('name', 'Administrador')->get();

        // Actualiza el estado de los eventos encontrados
        foreach ($usuarios as $event) {
            $event->update([
                'password' => Hash::make($event->email),
            ]);
            
            $informacionUsuario = new Informacion_votantes([
                'email' => $event->email,
                'nombre' => $event->name,
                'id_user' => $event->id,
                'identificacion' => $event->identificacion,
                'tipo' => 'Administrativos',
                'id_eventos' => 1,
                'candidato' => 0,
            ]);

            $informacionUsuario->save();
            
            $this->info('cerrado');
        }

        $this->info('Event statuses updated successfully.');
    }
}
