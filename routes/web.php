<?php

use App\Http\Controllers\ActaPresencialController;
use App\Http\Controllers\AnalisisController;
use App\Http\Controllers\Auth\FaceController;
use App\Http\Controllers\CandidatosController;
use App\Http\Controllers\cargueMasivoController;
use App\Http\Controllers\CedulaController;
use App\Http\Controllers\CertificadosController;
use App\Http\Controllers\DelegadosController;
use App\Http\Controllers\EventosController;
use App\Http\Controllers\JuradosController;
use App\Http\Controllers\ParametrosController;
use App\Http\Controllers\ParametrosDetalleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProyectosController;
use App\Http\Controllers\RegistroController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\TiposController;
use App\Http\Controllers\ValidacionesController;
use App\Http\Controllers\ValidationController;
use App\Http\Controllers\VotantesPresencialController;
use App\Http\Controllers\VotosController;
use App\Models\Eventos;
use App\Models\Hash_proyectos;
use App\Models\Hash_votantes;
use App\Models\Informacion_votantes;
use App\Models\ParametrosDetalle;
use App\Models\Votos;
use Carbon\Carbon;
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

// routes/api.php




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

//pagina consulta de certificados para descargar
Route::get('/certificado', function () {

    return Inertia::render('Certificados', []);
});


//Consultar por qr de carnet
Route::get('/consulta-certificado/{evento_id}/{votante_id}', function ($evento_id, $votante_id) {
    $voto = Votos::select('id', 'id_votante', 'id_eventos', 'isVirtual')
        ->with(['votante:id,nombre,tipo_documento,identificacion,comuna']) // corregido el with
        ->where('id_eventos', $evento_id)
        ->where('id_votante', $votante_id)
        ->firstOrFail();

    $comuna = ParametrosDetalle::select('id', 'detalle')->findOrFail($voto->votante->comuna);

    $evento = Eventos::select('id', 'nombre', 'fecha_inicio')->findOrFail($evento_id);

    return view('consulta_certificado', [
        'voto' => $voto,
        'evento' => $evento,
        'annio' => Carbon::parse($evento->fecha_inicio)->year,
        'comuna' => $comuna->detalle,
    ]);
})->name('consulta.certificado.publica');

//registro jurados
Route::get('/registro-jurados-@djdo33kc', [JuradosController::class, 'create'])->name('registro.jurados');
Route::post('/registro-jurados-@djdo33kc', [JuradosController::class, 'store'])->name('jurados.store');

//landing welcome
Route::get('/welcome', function () {
    return Inertia::render('Welcome', [
        'eventos' => Eventos::where('estado', 'Activo')->get(),
        'puntos_votacion' => ParametrosDetalle::where('estado', 1)
            ->where('codParametro', 'pt_v')
            ->get(),
    ]);
})->name('welcome');

Route::get('/dashboard', function () {


    $eventos_admin = Cache::remember('eventos_admin', 2, function () {
        return Eventos::whereNot('estado', 'Pendiente')->whereNot('nombre', 'Admin')->with('votos')->get();
    });

    $votantes = Cache::remember('votantes', 5, function () {
        return Hash_votantes::select('id_evento')->whereHas('votante.user.roles', function ($query) {
            $query->where('name', '!=', 'votoBlanco'); // Excluye roles con 'votoBlanco'
        })
            ->with(['votante.user.roles'])->get();
    });

    $info_votante = [];
    $eventos = [];
    $votos = [];
    if (Auth::user()->votantes) {
        $info_votante = Hash_votantes::where('id_votante', Auth::user()->votantes->id)->get();

        $eventos = Eventos::whereNot('nombre', '=', 'Admin')
            ->with(['votantes' => function ($query) {
                $query->where('id_votante', Auth::user()->votantes->id);
            }, 'evento_padre'])
            ->get();

        $votos = Votos::where('id_votante', Auth::user()->votantes->id)
        ->select('id_votante', 'id_eventos', 'id_candidato', 'id_proyecto')
        ->get();
    }



    // Obtén todos los tipos de los objetos en $info_votante
    $tipos = collect($info_votante)->pluck('tipo')->unique()->toArray();

    return Inertia::render('Dashboard', [
        'eventos' => $eventos,
        'votos' => $votos,
        'proyectos' => Hash_proyectos::with('proyecto')->get(),
        'candidatos' => Hash_votantes::where('candidato', 0)->whereIn('tipo', $tipos)->with('votante')->get(),
        'eventos_admin' => $eventos_admin,
        'votantes' => $votantes,
        'info_votante' => $info_votante ? $info_votante->where('subtipo', '!=', 0)->values() : 0,

    ]);
})->middleware(['auth', 'verified'])->name('dashboard');

// Route::get('/sse', function ()  {
//     $startTime = time();
//     return response()->stream(function () use ($startTime) {
//         $eventos_admin = Cache::remember('eventos_admin', 2, function () {
//             return Eventos::whereNot('estado', 'Pendiente')->whereNot('nombre', 'Admin')->with('votos')->get();
//         });

//         $votantes = Cache::remember('votantes', 5, function () {
//             return Informacion_votantes::select('id_eventos', 'nombre', 'tipo')->get();
//         });

//         foreach ($eventos_admin as $evento) {
//             echo "event: evento\n";
//             echo "data: " . json_encode($evento) . "\n\n";
//             ob_flush();
//             flush();
//         };

//         foreach ($votantes as $votante) {
//             echo "event: votante\n";
//             echo "data: " . json_encode($votante) . "\n\n";
//             ob_flush();
//             flush();
//         };


//         // Cerrar la conexión después de 30 segundos
//         if (time() - $startTime >= 30) {
//             return false; // Salir del bucle y cerrar la conexión
//         };

//         // Esperar antes de la próxima actualización
//         sleep(1); // Dormir durante 1 segundo para evitar la CPU en uso intensivo

//     }, 200, [
//         'Content-Type' => 'text/event-stream',
//         'Cache-Control' => 'no-cache',
//         'Connection' => 'keep-alive',
//     ]);
// });

Route::middleware('auth')->group(function () {
    Route::get('/about', fn() => Inertia::render('About'))->name('about');
    Route::get('users', [UserController::class, 'index'])->name('users.index');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');



    //update proyectos
    Route::post('/proyecto-update', [ProyectosController::class, 'updateProyecto'])->name('proyectoUpdate');
    //PLANTILLA EXCEL para cargue masivo
    //usuario
    Route::get('plantillaRes', [cargueMasivoController::class, 'plantillaRes'])->name('plantillaRes.excel');

    //import excel carga masiva
    //usuarios
    Route::post('/cargueVotantes', [CargueMasivoController::class, 'cargueVotantes'])->name('cargueVotantes');

    //historial registros
    Route::get('/historial_registros', [ValidacionesController::class, 'historial'])->name('historial_registros');
    //show registros segun historial
    Route::get('/historial_registros/{id}', [ValidacionesController::class, 'showHistorial'])->name('historial_registros.show');
    //GESTION REGISTROS
    Route::post('/aprobar-registro', [ValidacionesController::class, 'aprobarRegistro'])->name('aprobarRegistro');
    Route::post('/rechazar-registro', [ValidacionesController::class, 'rechazarRegistro'])->name('rechazarRegistro');
    Route::post('/desbloquear-registro', [ValidacionesController::class, 'desbloquearRegistro'])->name('desbloquearRegistro');

    //validar identificacion
    Route::post('/validar-identificacion-presencial', [ValidationController::class, 'verificar']);
    //actualizacion de datos con imgs
    Route::post('/corregir-datos', [ValidationController::class, 'corregirDatos'])->name('corregirDatos');

    //GESTION CERTIFICADOS
    //descargar
    Route::get('/certificados/descargar/{id}', [CertificadosController::class, 'descargarCertificado'])->name('certificados.descargar');

    //descargar excel
    Route::get('/votantes/exportar', [VotantesPresencialController::class, 'excel'])->name('votantes.excel');
});

Route::group(['middleware' => ['auth']], function () {
    Route::resource('users', UserController::class)->middleware('auth');
    Route::resource('roles', RoleController::class);
    Route::resource('tipos', TiposController::class);
    Route::resource('parametros', ParametrosController::class);
    Route::resource('parametrosDetalle', ParametrosDetalleController::class);
    Route::resource('proyectos', ProyectosController::class);

    Route::resource('eventos', EventosController::class);
    Route::resource('votos', VotosController::class);
    Route::resource('candidatos', CandidatosController::class);
    Route::resource('analisis', AnalisisController::class);

    Route::resource('gestion_registros', ValidacionesController::class);
    Route::resource('delegados', DelegadosController::class);
    Route::resource('corregir-registro', ValidationController::class);
    Route::resource('votantesPresencial', VotantesPresencialController::class);
    Route::resource('actaPresencial', ActaPresencialController::class);
});



require __DIR__ . '/auth.php';
