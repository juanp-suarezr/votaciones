<?php

namespace App\Providers;

use App\Models\Hash_votantes;
use App\Models\Votos;
use App\Observers\ValidacionesObserver;
use App\Observers\VotosObserver;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Votos::observe(VotosObserver::class);
        Hash_votantes::observe(ValidacionesObserver::class);
    }
}
