<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Request as RequestFacade;
use App\Http\Controllers\Controller;
use App\Models\Eventos;
use App\Models\Hash_votantes;
use App\Models\Informacion_votantes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class CandidatosController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    function __construct()
    {
        $this->middleware('permission:candidatos-listar|candidatos-crear|candidatos-editar|candidatos-eliminar', ['only' => ['index', 'store']]);
        $this->middleware('permission:candidatos-crear', ['only' => ['create', 'store']]);
        $this->middleware('permission:candidatos-editar', ['only' => ['edit', 'update']]);
        $this->middleware('permission:candidatos-eliminar', ['only' => ['destroy']]);
    }

    function index()
    {

        return Inertia::render(
            'Candidatos/Index',
            [
                'users' => Informacion_votantes::query()
                    ->when(RequestFacade::input('search'), function ($query, $search) {
                        $query->where('nombre', 'like', '%' . $search . '%')
                            ->orWhere('identificacion', 'like', '%' . $search . '%');
                    })
                    ->whereHas('hashVotantes', function ($query) {
                        $query->where('candidato', 1); // Filtrar por el campo candidato en hash_votantes
                    })
                    ->with('hashVotantes') // Traer la relación hashVotantes
                    ->paginate(5) // Aplica el paginado directamente aquí
                    ->withQueryString() // Mantener los parámetros en la URL
                    ->through(function ($user) {
                        // Concatenamos los tipos de hashVotantes en una cadena separada por comas
                        $user->tipos = $user->hashVotantes->pluck('tipo')
                            ->flatMap(function ($tipo) {
                                return explode('|', $tipo); // Dividir cada `tipo` en subtipos separados por '|'
                            })
                            ->unique() // Elimina valores duplicados
                            ->implode('|');
                        return $user;
                    }),
                'filters' => RequestFacade::only(['search', 'candidato']),

            ]
        );
    }

    public function create()
    {
        return Inertia::render(
            'Candidatos/Add',
            [
                'eventos' => Eventos::whereNot('nombre', 'Admin')
                    ->with(['votantes' => function ($query) {
                        // Filtra los votantes cuyo campo 'candidato' sea 0
                        $query->where('candidato', 0)
                            ->with('votante'); // Asegúrate de cargar la relación 'votante'
                    }])
                    ->get(),
            ]
        );
    }


    function store(Request $request)
    {

        // Implementar validación de formulario
        $request->validate([
            // 'empresa_id' => 'required|exists:empresas,id', // Validar existencia de empresa
            'imagen' => 'required|mimes:jpg,png,jpeg,gif,bmp,tiff,svg,web,webp|max:2048',

        ]);

        $candidatos = Informacion_votantes::findOrFail($request->user_id);

        //subir file
        if ($request->hasFile('imagen')) {

            $folder = 'usuarios';
            $extension = $request->file('imagen')->getClientOriginalExtension();

            $fileName = $candidatos->identificacion . '_candidatos.' . $extension;
            $filePath = $request->file('imagen')->storeAs('uploads/' . $folder, $fileName, 'public');

            // Eliminar el archivo antiguo si existe
            if ($candidatos->imagen != 'user.png') {
                Storage::delete('uploads/' . $folder . '/' . $candidatos->imagen);
            }

            // Actualizar la propiedad de imagen del eventos con el nombre del nuevo archivo
            $candidatos->imagen = $fileName;
        }

        // Guardar los cambios en la base de datos
        $candidatos->save();




        return to_route('candidatos.index');
    }

    public function update(Request $request, $evento)
    {
        // Validación de los campos requeridos
        $this->validate($request, [
            'eventos' => 'required',
            'candidatos' => 'required',
        ]);

        // Convertimos a array si `candidatos` no es un array
        $currentCandidatos = is_array($request->candidatos) ? $request->candidatos : [$request->candidatos];

        try {
            // Iteramos sobre los candidatos
            foreach ($currentCandidatos as $candidato) {
                // Verificamos si existe el registro en Hash_votantes
                $existingHash = Hash_votantes::where('id_votante', $candidato)
                    ->where('id_evento', $evento)
                    ->first();

                if ($existingHash) {
                    // Marcamos como candidato
                    $existingHash->candidato = 1;
                    $existingHash->save();
                } else {
                    // Lanzamos una excepción si no existe el hash correspondiente
                    throw new \Exception("No se encontró el hash para el votante con ID: $candidato en el evento especificado.");
                }
            }

            // Redireccionamos si todo va bien
            return Redirect::route('candidatos.create')->with('success', 'Candidatos actualizados exitosamente.');
        } catch (\Exception $e) {
            // Capturamos la excepción y devolvemos el mensaje de error a Inertia
            return Redirect::back()->with('error', $e->getMessage());
        }
    }
}
