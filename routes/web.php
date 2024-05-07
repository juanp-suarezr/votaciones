<?php

use App\Http\Controllers\AnalisisController;
use App\Http\Controllers\CandidatosController;
use App\Http\Controllers\EventosController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RegistroController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\TiposController;
use App\Http\Controllers\VotosController;
use App\Models\Eventos;
use App\Models\Informacion_votantes;
use App\Models\Votos;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    } else {
        return redirect()->route('login');
    }
});

Route::get('/home', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    } else {
        return redirect()->route('login');
    }
});


Route::get('/dashboard', function () {

    $eventos_admin = Eventos::where('estado', 'Activo')
        ->with('votos')
        ->get();
    $votantes = Informacion_votantes::select('id_eventos', 'nombre', 'tipo')->get();

    return Inertia::render('Dashboard', [
        'eventos' => Eventos::whereNot('nombre', '!=', 'Admin')
            ->with(['votantes' => function ($query) {
                $query->where('id_user', Auth::id());
            }])
            ->get(),
        'votos' => Votos::where('id_votante', Auth::user()->votantes->id)->select('id_votante', 'id_eventos', 'id_candidato')->get(),
        'candidatos' => Informacion_votantes::where('candidato', 1)->where('tipo', Auth::user()->votantes->tipo)->get(),

        'eventos_admin' => $eventos_admin,
        'votantes' => $votantes,
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/sse', function ()  {
    $startTime = time();
    return response()->stream(function () use ($startTime) {
        $eventos_admin = Cache::remember('eventos_admin', 5, function () {
            return Eventos::where('estado', 'Activo')->with('votos')->get();
        });

        $votantes = Cache::remember('votantes', 5, function () {
            return Informacion_votantes::select('id_eventos', 'nombre', 'tipo')->get();
        });

        foreach ($eventos_admin as $evento) {
            echo "event: evento\n";
            echo "data: " . json_encode($evento) . "\n\n";
            ob_flush();
            flush();
        };

        foreach ($votantes as $votante) {
            echo "event: votante\n";
            echo "data: " . json_encode($votante) . "\n\n";
            ob_flush();
            flush();
        };

        
        // Cerrar la conexión después de 30 segundos
        if (time() - $startTime >= 30) {
            return false; // Salir del bucle y cerrar la conexión
        };

        // Esperar antes de la próxima actualización
        sleep(1); // Dormir durante 1 segundo para evitar la CPU en uso intensivo

    }, 200, [
        'Content-Type' => 'text/event-stream',
        'Cache-Control' => 'no-cache',
        'Connection' => 'keep-alive',
    ]);
});

Route::middleware('auth')->group(function () {
    Route::get('/about', fn () => Inertia::render('About'))->name('about');
    Route::get('users', [UserController::class, 'index'])->name('users.index');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');


});

Route::group(['middleware' => ['auth']], function () {
    Route::resource('users', UserController::class)->middleware('auth');
    Route::resource('roles', RoleController::class);
    Route::resource('tipos', TiposController::class);

    Route::resource('eventos', EventosController::class);
    Route::resource('votos', VotosController::class);
    Route::resource('candidatos', CandidatosController::class);
    Route::resource('analisis', AnalisisController::class);
});



require __DIR__ . '/auth.php';
