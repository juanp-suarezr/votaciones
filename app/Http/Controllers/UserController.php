<?php

namespace App\Http\Controllers;

use App\Models\Eventos;
use App\Models\Hash_votantes;
use App\Models\Informacion_votantes;
use App\Models\Tipos;
use Illuminate\Support\Facades\Request as RequestFacade;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Spatie\Permission\Models\Role;
use App\Models\User;
use Inertia\Inertia;
use DB;
use Illuminate\Support\Facades\Cache;

class UserController extends Controller

{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    function __construct()
    {
        $this->middleware('permission:usuarios-listar|usuarios-crear|usuarios-editar|usuarios-eliminar', ['only' => ['index', 'store']]);
        $this->middleware('permission:usuarios-crear', ['only' => ['create', 'store']]);
        $this->middleware('permission:usuarios-editar', ['only' => ['edit', 'update']]);
        $this->middleware('permission:usuarios-eliminar', ['only' => ['destroy']]);
    }
    public function index()
    {
        return Inertia::render(
            'Users/Index',
            [
                'users' => User::query()
                    ->when(RequestFacade::input('search'), function ($query, $search) {
                        $query->where('name', 'like', '%' . $search . '%')
                            ->orWhere('email', 'like', '%' . $search . '%');
                    })
                    ->when(RequestFacade::input('estado_users'), function ($query, $estado) {
                        $query->where('estado', $estado);
                    })
                    ->with('roles') // Cargar los roles de cada usuario
                    ->with(['votantes.hashVotantes' => function ($query) {
                        // Cargar solo el campo `tipo` de hashVotantes
                        $query->select('id', 'tipo', 'id_votante'); // Ajusta los campos según tu estructura
                    }])
                    ->paginate(5)
                    ->withQueryString()
                    ->through(function ($user) {
                        // Concatenar los `tipo` de `hashVotantes` para cada usuario

                        $user->tipos = optional($user->votantes) // Aseguramos que votantes exista
                            ->hashVotantes->pluck('tipo')
                            ->flatMap(function ($tipo) {
                                return explode('|', $tipo); // Dividir cada `tipo` en subtipos separados por '|'
                            })
                            ->unique() // Elimina valores duplicados
                            ->implode('|');
                        return $user;
                    }),

                'filters' => RequestFacade::only(['search']),

            ]
        );
    }

    public function create()
    {
        $roles = Role::pluck('name', 'name')->all();
        return Inertia::render('Users/Add', [
            'tipos' => Tipos::get(),
            'roles' => $roles,
            'eventos' => Eventos::get(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|max:255||unique:' . User::class,
            'tipo' => 'required|string|max:60',
            'eventos' => 'required',
            'identificacion' => 'required|string|max:20|unique:' . Informacion_votantes::class,
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'roles_user' => 'required',
            'candidato' => 'required',
        ]);

        DB::beginTransaction(); // Iniciar la transacción

        try {
            // Crear el usuario
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            // Crear la información del votante asociada al usuario
            $informacionUsuario = new Informacion_votantes([
                'email' => $request->email,
                'nombre' => $request->name,
                'id_user' => $user->id,
                'identificacion' => $request->identificacion,
            ]);
            $user->votantes()->save($informacionUsuario);

            // Crear el registro de hash_votantes asociado a la información del votante
            $hash_votante = new Hash_votantes([
                'id_votante' => $informacionUsuario->id,
                'tipo' => $request->tipo,
                'id_evento' => $request->eventos,
                'candidato' => $request->candidato,
            ]);
            $informacionUsuario->hashVotantes()->save($hash_votante);

            // Asignar roles al usuario
            $user->syncRoles($request->input('roles_user'));

            // Confirmar la transacción
            DB::commit();
            Cache::forget('votantes');

            return to_route('users.index');
        } catch (\Exception $e) {
            // Si ocurre algún error, revertir todos los cambios
            DB::rollBack();

            // Puedes optar por lanzar el error o retornar un mensaje de error
            return redirect()->back()->withErrors(['error' => 'Error al crear el usuario: ' . $e->getMessage()]);
        }
    }

    public function edit(Request $request, $id_user)
    {
        $usuario = User::with(['votantes.hashVotantes' => function ($query) {
            // Cargar solo el campo `tipo` de hashVotantes
            $query->select('id', 'tipo', 'id_evento', 'id_votante'); // Ajusta los campos según tu estructura
        }])->findOrFail($id_user);

        // Concatenar los `tipo` de `hashVotantes` en el objeto usuario
        $usuario->tipos = optional($usuario->votantes) // Aseguramos que votantes exista
            ->hashVotantes->pluck('tipo') // Extraemos los `tipo` de cada `hashVotante`
            ->flatMap(function ($tipo) {
                return explode('|', $tipo); // Dividir cada `tipo` en subtipos separados por '|'
            })
            ->unique() // Elimina valores duplicados
            ->implode('|');

        $usuario->eventos = optional($usuario->votantes)
            ->hashVotantes->pluck('id_evento') // Extrae el campo `nombre` de cada `evento` relacionado
            ->unique() // Elimina duplicados, si es necesario
            ->implode('|');

        // Obtener roles y otros datos
        $roles = Role::pluck('name', 'name')->all();
        $userRole = $usuario->roles->pluck('name', 'name')->all();
        $tipos = Tipos::pluck('nombre', 'nombre')->all();
        $eventos = Eventos::select('id', 'nombre')->get();

        return Inertia::render('Users/Edit', compact('usuario', 'roles', 'userRole', 'tipos', 'eventos'));
    }

    public function update(Request $request, User $user)
    {

        $infoUser = Informacion_votantes::where('id_user', $user->id)->first();
        $hash_votante = Hash_votantes::where('id_votante', $infoUser->id)->get();
        // Validar los datos de entrada
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'max:255', Rule::unique('users')->ignore($user)],
            'tipo' => 'required|string|max:60',
            'identificacion' => ['required', 'string', 'max:20', Rule::unique('votantes')->ignore($infoUser)],
            'estado' => ['required'],
            'roles_user' => 'required',
        ]);


        $user->fill($data)->save();

        // Actualizar el modelo de info usuario con los datos validados
        $infoUser->nombre = $request->name;
        $infoUser->identificacion = $request->identificacion;
        $infoUser->save();

        foreach ($hash_votante as $hv) {

            // Aseguramos que $currentEventos sea un array, incluso si es un solo valor
            $currentEventos = is_array($hv->id_evento) ? $hv->id_evento : [$hv->id_evento];  // Convierte a array si es un solo valor

            // Los eventos nuevos recibidos en la solicitud
            $newEventos = $request->eventos;  // Este es un array con los IDs de los eventos

            // 1. Verificar los eventos nuevos y agregar los que no estén presentes
            foreach ($newEventos as $evento) {
                $existingHash = Hash_votantes::where('id_votante', $hv->id_votante)
                        ->where('id_evento', $evento)
                        ->first();

                        
                        if($existingHash) {
                    
                            $existingHash->tipo = $request->tipo;  // Asigna el tipo recibido
                            $existingHash->save();
                        }

                // Verificamos si el evento no está en los eventos actuales
                if (!in_array($evento, $currentEventos)) {
                    // Solo creamos el registro si no existe uno con el mismo id_votante e id_evento
                    
                    if (!$existingHash) {
                        Hash_votantes::create([
                            'id_votante' => $hv->id_votante,  // ID del votante
                            'id_evento' => $evento,  // ID del evento
                            'tipo' => $hv->tipo,  // Asignamos el tipo que ya tenemos
                        ]);
                    } 
                }
            }

            // 2. Eliminar los eventos que ya no están en la solicitud
            foreach ($currentEventos as $evento) {
                // Si el evento actual no está en los nuevos eventos, lo eliminamos
                if (!in_array($evento, $newEventos)) {
                    Hash_votantes::where('id_votante', $hv->id_votante)
                        ->where('id_evento', $evento)
                        ->delete();
                }
            }
        }



        $user->syncRoles($request->input('roles_user'));
        return Redirect::route('users.edit', $user->id);
    }

    public function destroy(Request $request, $id)
    {

        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = User::find($id);
        $user->estado = 'Bloqueado';
        $user->save();

        return redirect()->route('users.index')
            ->with('success', 'Recurso eliminado exitosamente');
    }
}
