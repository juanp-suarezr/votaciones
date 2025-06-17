<?php

namespace App\Http\Controllers;

use App\Models\Caninos;
use App\Models\Delegado;
use App\Models\InformacionUsuario;
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
use Illuminate\Support\Facades\Auth;

class DelegadosController extends Controller

{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    function __construct() {}



    public function index()
    {
        $user = Auth::user();

        $delegados = Delegado::paginate(5)->withQueryString();

        return Inertia::render(
            'Delegado/Index',
            [
                'delegados' => $delegados,
            ]
        );
    }

    public function create()
    {

        return Inertia::render('Delegado/Add');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:250',
            'cargo' => 'required|string|max:100',
            'firma' => 'required|image|mimes:pg,png,jpeg,jpg,gif,bmp,tiff,svg,web,webp|max:2048',
        ]);

        $fileName = 'NA';

        if ($request->hasFile('firma')) {
                $folder = 'delegado';
                $original = $request->file('firma');
                $extension = strtolower($original->getClientOriginalExtension());
                $fileName = time() . '_delegado_' . $request->nombre . '_' . $request->cargo . '.' . $extension;

                $rutaDestino = storage_path('app/public/uploads/' . $folder . '/' . $fileName);

                if (in_array($extension, ['jpg', 'jpeg'])) {
                    $img = imagecreatefromjpeg($original->getPathname());
                    imagejpeg($img, $rutaDestino, 60); // 70 es la calidad, puedes bajarla más si quieres
                    imagedestroy($img);
                } elseif ($extension === 'png') {
                    $img = imagecreatefrompng($original->getPathname());
                    imagepng($img, $rutaDestino, 7); // 0 (sin compresión) a 9 (máxima compresión)
                    imagedestroy($img);
                } else {
                    // Otros formatos, solo mover
                    $original->move(storage_path('app/public/uploads/' . $folder), $fileName);
                }
            }

        $delegado = Delegado::create([
            'nombre' => $request->nombre,
            'cargo' => $request->cargo,
            'estado' => 1,
            'firma' => $fileName
        ]);


        return to_route('delegado.index');
    }

    public function edit(Request $request, $id_user)
    {
        $usuario = User::findOrFail($id_user);
        $roles = Role::pluck('name', 'name')->all();
        $userRole = $usuario->roles->pluck('name', 'name')->all();
        return Inertia::render('Users/Edit', compact('usuario', 'roles', 'userRole'));
    }

    public function update(Request $request, User $user)
    {
        // Validar los datos de entrada
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'string', 'max:255', Rule::unique('users')->ignore($user)],
            'estado' => ['required'],
            'roles_user' => 'required'
        ]);
        // Actualizar el modelo de usuario con los datos validados
        $user->fill($data)->save();
        $user->syncRoles($request->input('roles_user'));
        return Redirect::route('users.edit', $user->id);
    }

    public function destroy(Request $request, $id)
    {

        $request->validate([
            'password' => ['required', 'current_password'],
        ]);
        DB::table("users")->where('id', $id)->delete();
        return redirect()->route('users.index')
            ->with('success', 'Recurso eliminado exitosamente');
    }
}
