<?php

use App\Exports\FuncionariosExports;
use App\Http\Controllers\ActaPresencialController;
use App\Http\Controllers\ActasVirtualesController;
use App\Http\Controllers\AnalisisController;
use App\Http\Controllers\AnalisisPresupuestoController;
use App\Http\Controllers\auditoriasController;
use App\Http\Controllers\Auth\FaceController;
use App\Http\Controllers\CandidatosController;
use App\Http\Controllers\cargueMasivoController;
use App\Http\Controllers\CargueVotantesFisicoController;
use App\Http\Controllers\CedulaController;
use App\Http\Controllers\CertificadosController;
use App\Http\Controllers\DelegadosController;
use App\Http\Controllers\EventosController;
use App\Http\Controllers\FuncionariosController;
use App\Http\Controllers\JuradosController;
use App\Http\Controllers\ParametrosController;
use App\Http\Controllers\ParametrosDetalleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProyectosController;
use App\Http\Controllers\RegistroController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\RutasController;
use App\Http\Controllers\TiposController;
use App\Http\Controllers\ValidacionesController;
use App\Http\Controllers\ValidationController;
use App\Http\Controllers\VotantesPresencialController;
use App\Http\Controllers\VotosController;
use App\Models\Acta_fin;
use App\Models\Acta_inicio;
use App\Models\Eventos;
use App\Models\Funcionarios_planeacion;
use App\Models\Hash_proyectos;
use App\Models\Hash_votantes;
use App\Models\Informacion_votantes;
use App\Models\Parametros;
use App\Models\ParametrosDetalle;
use App\Models\UsuariosBiometricos;
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
        return redirect()->route('welcome');
    }
});

Route::get('/home', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    } else {
        return redirect()->route('welcome');
    }
});

//pagina consulta de certificados para descargar
Route::get('/certificado', function () {

    return Inertia::render('Certificados', []);
});

//soporte envio solicitud
Route::post('/enviarSolicitud', [ValidacionesController::class, 'enviarSolicitud'])->name('enviarSolicitud');

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

//Consultar por qr de funcionario planeacion
Route::get('/consulta-funcionario/{identificacion}/{id}', function ($identificacion, $id) {

    $funcionario = Funcionarios_planeacion::where('identificacion', $identificacion)
        ->where('id', $id)
        ->firstOrFail();

    return view('consulta_funcionario', [
        'funcionario' => $funcionario,
    ]);
})->name('consulta.funcionario.publica');

//registro jurados
// Route::get('/registro-jurados-@djdo33kc', [JuradosController::class, 'create'])->name('registro.jurados');
// Route::post('/registro-jurados-@djdo33kc', [JuradosController::class, 'store'])->name('jurados.store');

//landing welcome
Route::get('/welcome', function () {
    return Inertia::render('Welcome', [
        'eventos' => Eventos::where('estado', 'Activo')->get(),
        'puntos_votacion' => ParametrosDetalle::where('estado', 1)
            ->where('codParametro', 'pt_v')
            ->get(),
        'isActive' => Eventos::where('estado', 'activo')
            ->whereRaw("LOWER(tipos) LIKE ?", ['%presupuesto participativo%'])
            ->exists(),
    ]);
})->name('welcome');

//landing comunas - barrio
Route::get('/cual-es-mi-sector', function () {
    return Inertia::render('ComunasBarrios', [

        'isActive' => Eventos::where('estado', 'activo')
            ->whereRaw("LOWER(tipos) LIKE ?", ['%presupuesto participativo%'])
            ->exists(),
    ]);
})->name('ComunasBarrios');

//GESTION CERTIFICADOS
//descargar
Route::get('/certificados/descargar/{id}/{idVotante?}/{id_padre?}', [CertificadosController::class, 'descargarCertificado'])->name('certificados.descargar');
//ver eventos a los que voto el votante segun identificacion
Route::get('/validar-certificado/{identificacion}', [VotosController::class, 'verificar']);

//pagina de resultados presupuesto participativo inicial sencilla
Route::get('/resultado-seleccionar-comuna', [AnalisisPresupuestoController::class, 'indexComuna']);
//pagina resultado para el ciudadano
Route::get('/resultado-presupuesto', [AnalisisPresupuestoController::class, 'ResultadosPresupuesto'])->name('resultado-presupuesto');

//pagina de resultados presupuesto participativo mas detallada
Route::get('/resultado-evento', [AnalisisPresupuestoController::class, 'index']);
Route::get('/resultado-comunas', [AnalisisPresupuestoController::class, 'ResultadosComunas'])->name('resultado-comunas');
Route::get('/resultado-proyectos', [AnalisisPresupuestoController::class, 'ResultadosProyectos'])->name('resultado-proyectos');
Route::get('/resultado-generales', [AnalisisPresupuestoController::class, 'ResultadosGenerales'])->name('resultado-generales');

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
        $info_votante = Hash_votantes::where('id_votante', Auth::user()->votantes->id)->with(['votante:id,Isdriver'])->get();

        $comunas_activas = ParametrosDetalle::where('codParametro', 'com01')
            ->where('estado', 1)
            ->pluck('id')
            ->toArray();

            $comuna_usuario = Auth::user()->votantes->comuna ?? null;

        $eventos = Eventos::whereNot('nombre', '=', 'Admin')
            ->with(['votantes' => function ($query) {
                $query->where('id_votante', Auth::user()->votantes->id);
            }, 'eventos_hijos.eventos' => function ($q) use ($comuna_usuario) {
                $q->withCount('hash_proyectos') // trae hash_proyectos_count en cada hijo
                    ->whereHas('hash_proyectos.proyecto', function ($q2) use ($comuna_usuario) {
                  $q2->where('subtipo', $comuna_usuario);
              });
            }])
            ->get()
            ->filter(function ($evento) use ($info_votante, $comunas_activas, $comuna_usuario) {
                // Verifica si el usuario tiene comuna activa
                return in_array($comuna_usuario, $comunas_activas);
            });

        $votos = Votos::where('id_votante', Auth::user()->votantes->id)
            ->select('id_votante', 'id_eventos', 'id_candidato', 'id_proyecto')
            ->get();
    }

    $existeActa = null;
    $cierre = null;
    $registro_biometrico = UsuariosBiometricos::where('user_id', Auth::user()->id)
    ->exists();
    if (Auth::user()->jurado) {




        $evento_padre = Eventos::where('id', Auth::user()->jurado->id_evento)
            ->with(['eventos_hijos.eventos' => function ($q) {
                $q->whereHas('hash_proyectos'); // solo trae los hijos que tienen proyectos
            }])
            ->first();

        foreach ($evento_padre->eventos_hijos as $event) {

            if ($existeActa === false) {
                continue;
            }
            if ($cierre === false) {
                continue;
            }
            $existeActa = Acta_inicio::where('id_jurado', Auth::user()->jurado->id)
                ->where('id_evento', $event->id_evento_hijo)
                ->exists();


            $cierre = Acta_fin::where('id_jurado', Auth::user()->jurado->id)
                ->where('id_evento', $event->id_evento_hijo)
                ->exists();
        }
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
        'existe_acta' => $existeActa,
        'cierre' => $cierre,
        'registro_biometrico' => $registro_biometrico,

    ]);
})->middleware(['auth', 'verified'])->name('dashboard');


Route::middleware('auth')->group(function () {
    Route::get('/about', fn() => Inertia::render('About'))->name('about');
    Route::get('users', [UserController::class, 'index'])->name('users.index');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    //cambiar estado isDrver votante
    Route::post('/marcar-driver', [UserController::class, 'marcarDriver'])->name('marcar-driver');


    //update proyectos
    Route::post('/proyecto-update', [ProyectosController::class, 'updateProyecto'])->name('proyectoUpdate');
    //PLANTILLA EXCEL para cargue masivo
    //usuario
    Route::get('plantillaRes', [cargueMasivoController::class, 'plantillaRes'])->name('plantillaRes.excel');
    //jurados
    Route::get('plantillaJur', [cargueMasivoController::class, 'plantillaJur'])->name('plantillaJur.excel');

    //import excel carga masiva
    //usuarios
    Route::post('/cargueVotantes', [CargueMasivoController::class, 'cargueVotantes'])->name('cargueVotantes');
    //jurados
    Route::post('/cargueJurados', [CargueMasivoController::class, 'cargueJurados'])->name('cargueJurados');

    //gestion registros desde gestor
    Route::get('/registro-gestion-administrativa', [ValidacionesController::class, 'registroGestion']);
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
    //validar face ia desde gestor
    Route::post('/face-validate-gestor', [FaceController::class, 'validateFace'])->name('face-validate-gestor');
    //recibir y crear usuario
    Route::post('/CreateUser', [ValidationController::class, 'verify'])->name('create-user');
    //usuarios duplicados
    Route::get('/usuarios-duplicados/{id_user}', [ValidationController::class, 'usuariosDuplicados'])->name('usuarios-duplicados');
    //actualizacion de datos con imgs
    Route::post('/corregir-datos', [ValidationController::class, 'corregirDatos'])->name('corregirDatos');
    //validar face ia
    Route::post('/face-validate-correccion', [FaceController::class, 'validateFaceCorreccion'])->name('face-validate-correccion');
    //actualizacion de datos delegado o jurado
    Route::post('/delegados-update', [DelegadosController::class, 'updateDelegados'])->name('delegados.updateDelegados');

    //AUDITORIAS
    Route::get('/auditorias', [auditoriasController::class, 'index'])->name('auditorias');
    Route::get('/auditoria-validaciones', [auditoriasController::class, 'auditoriaValidaciones'])->name('auditoria-validaciones');

    //iniciar acta presencial tic
    Route::get('/ActaInicial', [ActaPresencialController::class, 'actaInicial_create'])->name('ActaInicial.create');
    //cerrar acta presencial tic
    Route::get('/ActaCerrar', [ActaPresencialController::class, 'actaCerrar_create'])->name('ActaCerrar.create');

    //registro biometrico jurado
    Route::get('/registro-biometrico-jurado', [JuradosController::class, 'registroBiometrico'])->name('registro-biometrico-jurado');
    //validar face ia jurado
    Route::post('/face-validate-jurado', [FaceController::class, 'validateFaceJurado'])->name('face-validate-jurado');
    //Registrar biometricamente jurado
    Route::post('/registro-biometrico-jurado', [JuradosController::class, 'storeBiometrico'])->name('registro-biometrico-jurado');


    //Listar votaciones para jurado y votar
    Route::get('/votacionPresencial/eventos', [VotantesPresencialController::class, 'ShowEventos'])->name('votacionPresencial.eventos');
    //voto presencial electronico
    Route::get('/votos-jurados', [VotosController::class, 'indexJurado'])->name('votos-jurados.index');

    //descargar excel
    Route::get('/votantes/exportar', [VotantesPresencialController::class, 'excel'])->name('votantes.excel');
    //descargar excel funcionarios planeacion
    Route::get('/funcionarios/exportar', [FuncionariosController::class, 'zip'])->name('funcionarios.zip');

    //RUTAS PARA CARGUE VOTANTES FISICO
    Route::get('/votantesFisicos', [CargueVotantesFisicoController::class, 'index'])->name('votantesFisicos.index');
    Route::post('/votantesFisicos/cargueMasivo', [CargueVotantesFisicoController::class, 'cargueMasivo'])->name('votantesFisicos.cargueMasivo');
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
    Route::resource('actasVirtuales', ActasVirtualesController::class);
    Route::resource('registro-jurados', JuradosController::class);
    Route::resource('rutas', RutasController::class);

    //funcionarios planeacion
    Route::resource('funcionarios', FuncionariosController::class);
});



require __DIR__ . '/auth.php';
