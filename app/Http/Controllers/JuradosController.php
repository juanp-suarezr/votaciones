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
            'codigo_verificacion' => 'required|string|exists:verification_codes,code',
            'punto_votacion' => 'required|exists:parametros_detalle,id',
            'id_evento' => 'required|exists:eventos,id',
            'comuna' => 'required|exists:parametros_detalle,id',
            'firma' => 'nullable|image|mimes:pg,png,jpeg,jpg,gif,bmp,tiff,svg,web,webp|max:2048',
        ]);

        DB::beginTransaction();
        try {
            $fileName = 'NA';

            if ($request->hasFile('firma')) {
                $folder = 'delegado';
                $original = $request->file('firma');
                $extension = strtolower($original->getClientOriginalExtension());
                $fileName = time() . '_delegado_' . $request->nombre . '_' . $request->tipo . '.' . $extension;

                $rutaDestino = storage_path('app/public/uploads/' . $folder . '/' . $fileName);

                if (in_array($extension, ['jpg', 'jpeg'])) {
                    $img = imagecreatefromjpeg($original->getPathname());
                    imagejpeg($img, $rutaDestino, 60);
                    imagedestroy($img);
                } elseif ($extension === 'png') {
                    $img = imagecreatefrompng($original->getPathname());
                    imagepng($img, $rutaDestino, 7);
                    imagedestroy($img);
                } else {
                    $original->move(storage_path('app/public/uploads/' . $folder), $fileName);
                }
            }

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
                'firma' => $fileName,
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
}
