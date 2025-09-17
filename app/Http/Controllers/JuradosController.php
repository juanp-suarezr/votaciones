<?php

// app/Http/Controllers/CertificadosController.php
namespace App\Http\Controllers;

use App\Models\Caninos;
use App\Models\InformacionUsuario;
use App\Models\PageView;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Illuminate\Support\Facades\Request as RequestFacade;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

use App\Models\Delegado;
use App\Models\Delegados;
use App\Models\Eventos;
use App\Models\ParametrosDetalle;
use App\Models\User;
use App\Models\UsuariosBiometricos;
use App\Models\Votos;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use SimpleSoftwareIO\QrCode\Facades\QrCode;

class JuradosController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    function __construct() {}

    //register
    public function create(Request $request)
    {


        $eventos = Eventos::where('estado', 'Activo')->get();
        $parametros = ParametrosDetalle::where('estado', 1)
            ->where('codParametro', 'pt_v')
            ->get();

        return Inertia::render('Auth/RegistroJurados', [

            'eventos' => $eventos,
            'puntos_votacion' => $parametros
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:200',
            'identificacion' => 'required|string|max:20|unique:delegados,identificacion',
            'contacto' => 'required|string|max:20',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8',
            'punto_votacion' => 'required|exists:parametros_detalle,id',
            'id_evento' => 'required|exists:eventos,id',
            'comuna' => 'required|exists:parametros_detalle,id',
        ]);

        DB::beginTransaction();
        try {

            $user = User::create([
                'name' => $request->nombre,
                'email' => $request->email,
                'password' => bcrypt($request->password),
            ]);

            $delegado = Delegados::create([
                'id_evento' => $request->id_evento,
                'id_user' => $user->id,
                'nombre' => $request->nombre,
                'identificacion' => $request->identificacion,
                'contacto' => $request->contacto,
                'tipo' => 'Jurado',
                'cargo' => 'Jurado',
                'comuna' => $request->comuna,
                'puntos_votacion' => $request->punto_votacion,

            ]);

            // Asignar el rol de jurado al usuario
            $user->assignRole('Jurado');

            DB::commit();
            return redirect()->back()->with('success', 'Jurado registrado correctamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al registrar jurado: ' . $e->getMessage());
            return redirect()->back()->withErrors('error', 'Error al registrar el jurado.');
        }
    }

    //registro biometrico
    public function registroBiometrico()
    {

        return Inertia::render('Delegado/RegistroBiometricoJurados', []);
    }

    //registrar biometrico jurado
    public function storeBiometrico(Request $request)
    {

        $request->validate([

            'cedula_front' => 'required|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'checked' => 'required',
            'declaracion' => 'required',

        ]);





        // Verificacion sencilla campo obligatorio
        if ($request->campoObligatorio != null && $request->campoObligatorio != '') {
                return redirect()->back()->withErrors(['error' => 'Seguro no eres un robot. Inténtelo nuevamente.']);
            }


        // Crear el usuario
        try {

            // Obtener la extensión original de los archivos
            $frontExtension = $request->file('cedula_front')->getClientOriginalExtension();



            $folderDoc = 'documentos';
            $fileNameFront = 'cedula_front_' . Auth::user()->jurado->identificacion . '.' . $frontExtension;

            // Guardar los archivos con su extensión original
            $frontPath = $request->file('cedula_front')->storeAs('uploads/' . $folderDoc, $fileNameFront, 'public');
            // $backPath = $request->file('cedula_back')->storeAs('uploads/' . $folderDoc, $fileNameBack, 'public');

            //foto evidencia
            $folderPhoto = 'fotos';
            $fileNamePhoto = 'foto' . Auth::user()->jurado->identificacion . '.' . $request->file('photo')->getClientOriginalExtension();

            $fotoPath = $request->file('photo')->storeAs('uploads/' . $folderPhoto, $fileNamePhoto, 'public');


            $validacion = $request->validaciones;
            // Log::info('embedding', ['embedding' => json_encode($request->embedding)]);



            if ($request->embedding && ($validacion == "")) {

                //CREAR REGISTRO BIOMETRICO
                $registroBiometrico = new UsuariosBiometricos([
                    'user_id' => Auth::user()->id,
                    'embedding' => json_encode($request->embedding), // convertir a string JSON
                    'photo' => $fileNamePhoto,
                    'cedula_front' => $fileNameFront,
                    'motivo' => 'Validación exitosa',
                    'estado' => 'Validado',

                ]);
                $registroBiometrico->save();


            } else {

                //CREAR REGISTRO BIOMETRICO no validado
                $registroBiometrico = new UsuariosBiometricos([
                    'user_id' => Auth::user()->id,
                    'embedding' => json_encode($request->embedding), // convertir a string JSON
                    'photo' => $fileNamePhoto,
                    'cedula_front' => $fileNameFront,
                    'estado' => 'Pendiente',
                    'motivo' => $validacion,

                ]);
                $registroBiometrico->save();
            }


            // Confirmar la transacción
            DB::commit();

            return back();
        } catch (\Exception $e) {
            // Si ocurre algún error, revertir todos los cambios
            DB::rollBack();
            // Eliminar el código de verificación
            // $verification->delete();

            // Puedes optar por lanzar el error o retornar un mensaje de error
            return redirect()->back()->withErrors(['error' => 'Error al crear el usuario: ' . $e->getMessage()]);
        }
    }
}
