<?php

namespace App\Http\Controllers;

use App\Models\Eventos;
use App\Models\Hash_proyectos;
use App\Models\Hash_votantes;
use App\Models\Informacion_votantes;
use App\Models\Parametros;
use App\Models\ParametrosDetalle;
use App\Models\Proyectos;
use App\Models\Tipos;
use Illuminate\Support\Facades\Request as RequestFacade;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Spatie\Permission\Models\Role;

use Inertia\Inertia;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProyectosController extends Controller

{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    function __construct()
    {
        $this->middleware('permission:eventos-listar|eventos-crear|eventos-editar|eventos-eliminar', ['only' => ['index', 'store']]);
        $this->middleware('permission:eventos-crear', ['only' => ['create', 'store']]);
        $this->middleware('permission:eventos-editar', ['only' => ['edit', 'update']]);
        $this->middleware('permission:eventos-eliminar', ['only' => ['destroy']]);
    }

    public function index()
    {

        $estado = RequestFacade::input('estado');


        return Inertia::render(
            'Proyectos/Index',
            [
                'proyectos' => Proyectos::query()
                    ->when(RequestFacade::input('search'), function ($query, $search) {
                        $query->where('detalle', 'like', '%' . $search . '%')
                            ->orWhere('tipo', $search);
                    })
                    ->when($estado !== null, function ($query) use ($estado) {
                        $query->where('estado', $estado);
                    })
                    ->with('parametroDetalle')
                    ->with('hash_proyectos')
                    ->paginate(5)
                    ->withQueryString(),



                'filters' => RequestFacade::only(['search', 'estado']),

            ]
        );
    }

    public function create()
    {
        $tipos = Tipos::pluck('nombre', 'nombre')->all();
        $parametros = Parametros::where('estado', 1)->get();
        $subtipos = ParametrosDetalle::where('estado', 1)->get();
        $eventos = Eventos::where('tipos', 'Proyecto')->get();

        return Inertia::render('Proyectos/Add', [
            'tipos' => $tipos,
            'parametros' => $parametros,
            'subtipos' => $subtipos,
            'eventos' => $eventos,

        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'detalle' => 'required|string|max:100',
            'descripcion' => 'required|string|max:500',
            'tipo' => 'required',
            'subtipo' => 'required',
            'numero_tarjeton' => 'required',
            'estado' => 'required',

        ]);

        $fileName = 'NA';


        DB::beginTransaction(); // Iniciar la transacción


        try {

            if ($request->imagen) {

                $request->validate([

                    'imagen' => 'mimes:jpg,png,jpeg,gif,bmp,tiff,svg,web,webp|max:2048',

                ]);


                $folder = 'proyectos';
                $extension = $request->file('imagen')->getClientOriginalExtension();

                $fileName = time() . '_proyecto_' . $request->tipo . '_' . $request->subtipo . '.' . $extension;
                $filePath = $request->file('imagen')->storeAs('uploads/' . $folder, $fileName, 'public');
            }

            // Crear el usuario
            $proyecto = Proyectos::create([
                'detalle' => $request->detalle,
                'descripcion' => $request->descripcion,
                'tipo' => $request->tipo,
                'subtipo' => $request->subtipo,
                'numero_tarjeton' => $request->numero_tarjeton,
                'imagen' => $fileName,
                'estado' => $request->estado,
            ]);

            //save
            $proyecto->save();

            foreach ($request->eventos as $evento) {

                Hash_proyectos::create([
                    'id_proyecto' => $proyecto->id,  // ID del votante
                    'id_evento' => $evento,  // ID del evento
                    'tipo' => $request->tipo,  // Asignamos el tipo que ya tenemos
                    'subtipo' => $request->subtipo,  // Asignamos el subtipo que ya tenemos
                ]);
            }


            // Confirmar la transacción
            DB::commit();


            return back()->with('success', 'Proyecto creado exitosamente');
        } catch (\Exception $e) {
            // Si ocurre algún error, revertir todos los cambios
            DB::rollBack();

            // Puedes optar por lanzar el error o retornar un mensaje de error
            return redirect()->back()->withErrors(['error' => 'Error al crear el parametro: ' . $e->getMessage()]);
        }
    }


    public function edit(Request $request, $id_proyecto)
    {

        $tipos = Tipos::pluck('nombre', 'nombre')->all();
        $parametros = Parametros::where('estado', 1)->get();
        $subtipos = ParametrosDetalle::where('estado', 1)->get();
        $eventos = Eventos::where('tipos', 'Proyecto')->get();
        $proyecto = Proyectos::with('hash_proyectos')->find($id_proyecto);

        $proyecto->eventos = optional($proyecto->hash_proyectos)
            ->pluck('id_evento') // Extrae el campo `nombre` de cada `evento` relacionado
            ->unique() // Elimina duplicados, si es necesario
            ->implode('|');

        return Inertia::render('Proyectos/Edit', [
            'tipos' => $tipos,
            'parametros' => $parametros,
            'subtipos' => $subtipos,
            'eventos' => $eventos,
            'proyecto' => $proyecto,

        ]);
    }

    public function updateProyecto(Request $request)
    {


        $hash_proyectos = Hash_proyectos::where('id_proyecto', $request->id)->get();
        $proyecto = Proyectos::find($request->id);

        $request->validate([
            'detalle' => 'required|string|max:100',
            'descripcion' => 'required|string|max:500',
            'tipo' => 'required',
            'subtipo' => 'required',
            'numero_tarjeton' => 'required',
            'estado' => 'required',

        ]);

        DB::beginTransaction(); // Iniciar la transacción

        try {

            $fileName = null;

            if ($request->imagen) {

                $request->validate([

                    'imagen' => 'mimes:jpg,png,jpeg,gif,bmp,tiff,svg,web,webp|max:2048',

                ]);


                $folder = 'proyectos';
                $extension = $request->file('imagen')->getClientOriginalExtension();

                $fileName = time() . '_proyecto_' . $request->tipo . '_' . $request->subtipo . '.' . $extension;
                $filePath = $request->file('imagen')->storeAs('uploads/' . $folder, $fileName, 'public');

                // Eliminar el archivo antiguo si existe
            if ($proyecto->imagen != 'NA') {
                Storage::delete('uploads/' . $folder . '/' . $proyecto->imagen);
            }


            }

            // Crear el usuario
            $proyecto->update([
                'detalle' => $request->detalle,
                'descripcion' => $request->descripcion,
                'tipo' => $request->tipo,
                'subtipo' => $request->subtipo,
                'numero_tarjeton' => $request->numero_tarjeton,
                'imagen' => $fileName ?? $proyecto->imagen,
                'estado' => $request->estado,
            ]);

            foreach ($hash_proyectos as $hv) {

                // Aseguramos que $currentEventos sea un array, incluso si es un solo valor
                $currentEventos = is_array($hv->id_evento) ? $hv->id_evento : [$hv->id_evento];  // Convierte a array si es un solo valor

                // Los eventos nuevos recibidos en la solicitud
                $newEventos = $request->eventos;  // Este es un array con los IDs de los eventos

                // 1. Verificar los eventos nuevos y agregar los que no estén presentes
                foreach ($newEventos as $evento) {
                    $existingHash = Hash_proyectos::where('id_proyecto', $hv->id_proyecto)
                            ->where('id_evento', $evento)
                            ->first();


                    // Verificamos si el evento no está en los eventos actuales
                    if (!in_array($evento, $currentEventos)) {
                        // Solo creamos el registro si no existe uno con el mismo id_votante e id_evento

                        if (!$existingHash) {
                            Hash_proyectos::create([
                                'id_proyecto' => $hv->id_proyecto,  // ID del votante
                                'id_evento' => $evento,  // ID del evento
                                'tipo' => $hv->tipo,  // Asignamos el tipo que ya tenemos
                                'subtipo' => $hv->subtipo,  // Asignamos el subtipo que ya tenemos
                            ]);
                        }
                    }
                }

                // 2. Eliminar los eventos que ya no están en la solicitud
                foreach ($currentEventos as $evento) {
                    // Si el evento actual no está en los nuevos eventos, lo eliminamos
                    if (!in_array($evento, $newEventos)) {
                        Hash_proyectos::where('id_proyecto', $hv->id_proyecto)
                            ->where('id_evento', $evento)
                            ->delete();
                    }
                }
            }

            //save
            $proyecto->save();

            // Confirmar la transacción
            DB::commit();

            return back()
                ->with('success', 'Parametro actualizado exitosamente');
        } catch (\Exception $e) {
            // Si ocurre algún error, revertir todos los cambios
            DB::rollBack();

            // Puedes optar por lanzar el error o retornar un mensaje de error
            return redirect()->back()->withErrors(['error' => 'Error al actualizar el parametro: ' . $e->getMessage()]);
        }
    }

    public function destroy(Request $request, $id)
    {

        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $parametro = User::find($id);
        $parametro->estado = 'Bloqueado';
        $parametro->save();

        return back()
            ->with('success', 'Recurso eliminado exitosamente');
    }
}
