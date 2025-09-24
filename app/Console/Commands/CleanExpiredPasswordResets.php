<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanExpiredPasswordResets extends Command
{
    protected $signature = 'password_resets:clean';
    protected $description = 'Elimina tokens de reseteo de contraseña expirados';

    public function handle()
    {
        $expireMinutes = config('auth.passwords.users.expire'); // por defecto 60
        $limit = Carbon::now()->subMinutes($expireMinutes);

        DB::table('password_resets')
            ->where('created_at', '<', $limit)
            ->delete();

        $this->info('Tokens expirados eliminados correctamente.');
    }
}
