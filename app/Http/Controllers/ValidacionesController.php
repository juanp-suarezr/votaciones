<?php

namespace App\Http\Controllers;

use App\Mail\InscriptionApprovedMail;
use App\Mail\InscriptionDisapprovedMail;
use App\Models\Caninos;
use App\Models\Certificados;
use App\Models\Delegado;
use App\Models\Hash_votantes;
use App\Models\InformacionUsuario;
use Illuminate\Support\Facades\Request as RequestFacade;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Spatie\Permission\Models\Role;
use App\Models\User;
use App\Models\UsuariosBiometricos;
use Barryvdh\DomPDF\Facade\Pdf;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class ValidacionesController extends Controller

{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    function __construct()
    {
        $this->middleware('permission:usuarios-listar', ['only' => ['index', 'show']]);
        $this->middleware('permission:usuarios-crear', ['only' => ['aprobarRegistro', 'rechazarRegistro']]);
    }


    public function index()
    {
        $user = Auth::user();


        $votantes = Hash_votantes::query()
            ->where('id_evento', 15)
            ->when(RequestFacade::input('subtipo'), function ($query) {
                $subtipo = RequestFacade::input('subtipo.value');
                $query->where('subtipo', 'like', $subtipo);
            })->whereHas('votante', function ($query) use ($user) {

                $identificacion = RequestFacade::input('identificacion');
                $nombre = RequestFacade::input('nombre');

                if ($identificacion) {
                    $query->where('identificacion', $identificacion);
                }

                if ($nombre) {
                    $query->where('nombre', 'like', '%' . $nombre . '%');
                }
            })
            ->with('votante.user.biometrico', 'evento')
            ->where('estado', 'Pendiente')
            ->paginate(5)
            ->withQueryString();









        return Inertia::render(
            'GestionRegistros/Index',
            [
                'votantes' => $votantes,

                'filters' => RequestFacade::only(['identificacion', 'nombre', 'subtipo']),
            ]
        );
    }

    public function show($id)
    {


        $votante = Hash_votantes::query()
            ->with('votante.user.biometrico')->findOrFail($id);


        //duplicados
        $raw = $votante->votante->user->biometrico->embedding;

        $usuarios = UsuariosBiometricos::select('id', 'embedding', 'photo', 'estado', 'user_id')
            ->where('estado', 'Validado')
            ->where('user_id', '!=', $votante->votante->user->id)
            ->get();




        $duplicados = [];
        $distancias = [];

        if ($raw && $raw !== 'NA') {

            // Parsear embedding recibido
            $data = json_decode($raw, true);

            $input = collect($data);


            if ($input->count() !== 128) {
                Log::warning('El embedding tiene dimensiones incorrectas.', [
                    'input_count' => $input->count(),
                    'usuario_id' => $votante->votante->user->id,
                ]);
            }

            foreach ($usuarios as $usuario) {
                $stored = collect(json_decode($usuario->embedding, true));


                // Validar que ambos embeddings tienen la misma longitud
                if ($input->count() !== $stored->count()) {
                    Log::warning("Dimensiones incompatibles entre embeddings. ID usuario: {$usuario->id}");
                    continue;
                }

                // Calcular distancia Euclidiana
                $distance = sqrt($input->zip($stored)->reduce(function ($acc, $pair) {
                    $pair = $pair->toArray(); // 👈 convertir a array directamente

                    if (count($pair) === 2 && is_numeric($pair[0]) && is_numeric($pair[1])) {
                        $diff = $pair[0] - $pair[1];

                        return $acc + pow($diff, 2);
                    } else {
                        Log::warning('Par inválido en zip:', ['pair' => $pair]);
                    }

                    return $acc;
                }, 0));


                $distancias[] = [
                    'id' => $usuario->id,
                    'distance' => $distance
                ];
                

                // Si es duplicado (distancia < 0.5)
                if ($distance < 0.5) {
                    Log::info('duplicado');
                    Log::info('usuario', ['usuario' => $usuario]);
                    $duplicados[] = $usuario; // O `$usuario->toArray()` si lo necesita como array
                }
            }
        } else {
            Log::info('Embedding no disponible o es NA.');
        }

        return Inertia::render(
            'GestionRegistros/Show',
            [
                'votante' => $votante,
                'duplicados' => $duplicados,
            ]
        );
    }

    //aprobar registro
    public function aprobarRegistro(Request $request)
    {

        DB::beginTransaction();
        try {

            $votante = Hash_votantes::query()
                ->with('votante.user')->findOrFail($request->id);



            $votante->estado = 'Activo';
            $votante->save();


            Mail::to($votante->votante->user->email)->send(new InscriptionApprovedMail($votante));

            DB::commit();
            return back()->with('message', 'registro aprobado con éxito.');;
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Ocurrió un error durante el proceso. Inténtelo nuevamente. ' . $e]);
        }
    }

    //rechazar registro
    public function rechazarRegistro(Request $request)
    {

        DB::beginTransaction();
        try {
            $votante = Hash_votantes::query()
                ->with('votante.user')->findOrFail($request->id);



            $votante->estado = 'Rechazado';
            $votante->motivo = $request->motivo;
            $votante->save();


            Mail::to($votante->votante->user->email)->send(new InscriptionDisapprovedMail($votante));

            DB::commit();
            return back()->with('message', 'registro rechazado exitosamente.');;
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Ocurrió un error durante el proceso. Inténtelo nuevamente. ' . $e]);
        }
    }
}
