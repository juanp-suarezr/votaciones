<?php

namespace App\Http\Controllers;

use App\Models\Eventos;
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
        return Inertia::render('Users/Index', 
        [
            'users' => User::query()
                ->when(RequestFacade::input('search'),function($query, $search) {
                    $query->where('name','like','%'.$search.'%')
                    ->OrWhere('email','like','%'.$search.'%');
                })->when(RequestFacade::input('estado_users'), function ($query, $estado) {
                    $query->where('estado', $estado);
                })->with('roles')->with('votantes')->paginate(5)
                ->withQueryString(),
                'filters' => RequestFacade::only(['search']),
                
        ]);
    }

    public function create()
    {
        $roles = Role::pluck('name', 'name')->all();
        return Inertia::render('Users/Add',[
            'tipos' => Tipos::get(),
            'roles'=>$roles,
            'eventos' => Eventos::get(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|max:255||unique:'.User::class,
            'tipo' => 'required|string|max:60',
            'eventos' => 'required',
            'identificacion' => 'required|string|max:20|unique:'.Informacion_votantes::class,
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'roles_user' => 'required'
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $informacionUsuario = new Informacion_votantes([
            'email' => $request->email,
            'nombre' => $request->name,
            'id_user' => $user->id,
            'identificacion' => $request->identificacion,
            'tipo' => $request->tipo,
            'id_eventos' => $request->eventos,
        ]);

        // Asocia la información del usuario
        $user->votantes()->save($informacionUsuario);

        $user->syncRoles($request->input('roles_user'));

        return to_route('users.index');
    }

    public function edit(Request $request,$id_user)
    {
        $usuario = User::with('votantes')->findOrFail($id_user);
        $roles = Role::pluck('name', 'name')->all();
        $userRole = $usuario->roles->pluck('name', 'name')->all();
        $tipos = Tipos::get();
        $eventos = Eventos::get();

        return Inertia::render('Users/Edit', compact('usuario','roles','userRole', 'tipos', 'eventos'));
    }

    public function update(Request $request, User $user)
    {

        $infoUser = Informacion_votantes::where('id_user', $user->id)->first();
        // Validar los datos de entrada
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'max:255', Rule::unique('users')->ignore($user)],
            'tipo' => 'required|string|max:60',
            'identificacion' => ['required', 'string', 'max:20', Rule::unique('votantes')->ignore($infoUser)],
            'estado' => ['required'],
            'roles_user' => 'required'
        ]);


        $user->fill($data)->save();
        
        // Actualizar el modelo de info usuario con los datos validados
        $infoUser->nombre = $request->name;
        $infoUser->identificacion = $request->identificacion;
        $infoUser->tipo = $request->tipo;
        $infoUser->id_eventos = $request->eventos;
        $infoUser->save();

        $user->syncRoles($request->input('roles_user'));
        return Redirect::route('users.edit',$user->id);
    }

    public function destroy(Request $request,$id){

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
