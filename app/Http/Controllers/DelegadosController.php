<?php

namespace App\Http\Controllers;

use App\Models\Caninos;
use App\Models\Delegado;
use App\Models\Delegados;
use App\Models\Eventos;
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
use Illuminate\Console\Scheduling\Event;
use Illuminate\Support\Facades\Auth;

class DelegadosController extends Controller

{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    function __construct()
    {
        $this->middleware('permission:delegados-listar|delegados-crear|delegados-editar|delegados-eliminar', ['only' => ['index', 'show']]);
        $this->middleware('permission:delegados-crear', ['only' => ['create', 'store']]);
        $this->middleware('permission:delegados-editar', ['only' => ['edit', 'update']]);
        $this->middleware('permission:delegados-eliminar', ['only' => ['destroy']]);
    }



    public function index()
    {

        $delegados = Delegados::paginate(5)->withQueryString();

        return Inertia::render(
            'Delegado/Index',
            [
                'delegados' => $delegados,
            ]
        );
    }

    public function create()
    {



        return Inertia::render('Delegado/Add', []);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:200',
            'cargo' => 'required|string|max:150',
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



        $delegado = Delegados::create([
            'nombre' => $request->nombre,
            'cargo' => $request->cargo,
            'estado' => 1,
            'tipo' => $request->tipo,
            'firma' => $fileName
        ]);


        return to_route('delegados.index');
    }

    public function edit(Request $request, $id)
    {
        $delegado = Delegados::findOrFail($id);

        return Inertia::render('Delegado/Edit', compact('delegado'));
    }

    public function updateDelegados(Request $request)
    {

        $request->validate([
            'nombre' => 'required|string|max:200',
            'cargo' => 'required|string|max:150',

        ]);

        $delegados = Delegados::findOrFail($request->id);


        $fileName = 'NA';


        if ($request->hasFile('firma') && $delegados->tipo == "secretario") {
            $request->validate([
                'firma' => 'required|image|mimes:pg,png,jpeg,jpg,gif,bmp,tiff,svg,web,webp|max:2048',
            ]);
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

        $delegados->nombre = $request->nombre;
        $delegados->cargo = $request->cargo;
        $delegados->firma = $fileName != 'NA' ? $fileName : $delegados->firma;
        $delegados->save();

        return Redirect::back();
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
