<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Request as RequestFacade;
use App\Http\Controllers\Controller;
use App\Models\Informacion_votantes;
use Illuminate\Http\Request;
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
                            ->OrWhere('email', 'like', '%' . $search . '%');
                    })->when(RequestFacade::input('candidato'), function ($query, $candidato) {
                        $query->where('candidato', $candidato);
                    })->paginate(5)
                    ->withQueryString(),
                'filters' => RequestFacade::only(['search', 'candidato']),

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
}
