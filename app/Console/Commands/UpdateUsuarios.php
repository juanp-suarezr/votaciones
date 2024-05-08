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

        $id_especifico = 252;
        $usuarios = User::with('votantes')->whereNot('name', 'Administrador')->where('id', '>=', $id_especifico)->get();

        // Actualiza el estado de los eventos encontrados
        foreach ($usuarios as $event) {
            $event->update([
                'password' => Hash::make($event->email),
            ]);
            $event->syncRoles('Usuarios');

            $exist = Informacion_votantes::where('id_user', $event->id)->first();

            if ($exist) {
            } else {
                $informacionUsuario = new Informacion_votantes([
                    'email' => $event->email,
                    'nombre' => $event->name,
                    'id_user' => $event->id,
                    'identificacion' => $event->email,
                    'tipo' => 'Docentes',
                    'id_eventos' => 1,
                    'candidato' => 0,
                ]);

                $informacionUsuario->save();
            }



            $this->info($event->id);
        }

        $this->info('Event statuses updated successfully.');
    }
}
