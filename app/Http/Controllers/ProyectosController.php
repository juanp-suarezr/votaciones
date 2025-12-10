<?php

namespace App\Http\Controllers;

use App\Models\Eventos;
use App\Models\Hash_proyectos;
use App\Models\Hash_votantes;
use App\Models\Informacion_votantes;
use App\Models\Parametros;
use App\Models\ParametrosDetalle;
use App\Models\Proyectos;
use App\Models\Tipo_proyectos;
use App\Models\Tipos;
use App\Models\User;
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
                    ->with('tipo_proyecto')
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
        $eventos = Eventos::where('tipos', 'LIKE', '%Proyecto%')->where('estado', '!=', 'Bloqueado')->get();
        $tipo_proyectos = Tipo_proyectos::select('nombre', 'id')->get();

        return Inertia::render('Proyectos/Add', [
            'tipos' => $tipos,
            'parametros' => $parametros,
            'subtipos' => $subtipos,
            'eventos' => $eventos,
            'tipo_proyectos' => $tipo_proyectos,

        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'detalle' => 'required|string|max:500',
            'descripcion' => 'required|string|max:600',
            'tipo' => 'required',
            'subtipo' => 'required',
            'numero_tarjeton' => 'required',
            'estado' => 'required',
            'tipo_proyecto' => 'required',

        ]);

        $fileName = 'NA';


        DB::beginTransaction(); // Iniciar la transacción


        try {

            // Crear el usuario
            $proyecto = Proyectos::create([
                'detalle' => $request->detalle,
                'descripcion' => $request->descripcion,
                'tipo' => $request->tipo,
                'subtipo' => $request->subtipo,
                'numero_tarjeton' => $request->numero_tarjeton,
                'id_tipo' => $request->tipo_proyecto,
                'estado' => $request->estado,
            ]);

            //save
            $proyecto->save();

            foreach ($request->eventos as $evento) {

                Hash_proyectos::create([
                    'id_proyecto' => $proyecto->id,  // ID del votante
                    'id_evento' => $evento,  // ID del evento
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
        $eventos = Eventos::where('tipos', 'LIKE', '%Proyecto%')->get();
        $proyecto = Proyectos::with('hash_proyectos')->find($id_proyecto);
        $tipo_proyectos = Tipo_proyectos::select('nombre', 'id')->get();

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
            'tipo_proyectos' => $tipo_proyectos,
        ]);
    }

    public function updateProyecto(Request $request)
    {


        $hash_proyectos = Hash_proyectos::where('id_proyecto', $request->id)->get();
        $proyecto = Proyectos::find($request->id);

        $request->validate([
            'detalle' => 'required|string|max:400',
            'descripcion' => 'required|string|max:600',
            'tipo' => 'required',
            'subtipo' => '',
            'numero_tarjeton' => 'required',
            'estado' => 'required',
            'tipo_proyecto' => 'required',

        ]);

        DB::beginTransaction(); // Iniciar la transacción


        try {


            // Crear el usuario
            $proyecto->update([
                'detalle' => $request->detalle,
                'descripcion' => $request->descripcion,
                'tipo' => $request->tipo,
                'subtipo' => $request->input('subtipo.id', 0), // Aseguramos que subtipo tenga un valor por defecto si no se envía
                'numero_tarjeton' => $request->numero_tarjeton,
                'id_tipo' => $request->tipo_proyecto,
                'estado' => $request->estado,
            ]);

            if ($hash_proyectos->isEmpty()) {
                // Si no hay hash_proyectos, creamos uno nuevo

                foreach ($request->eventos as $idEvento) {
                    if($idEvento == null || $idEvento == 'NaN') continue; // Evitar insertar eventos nulos
                    Hash_proyectos::create([
                        'id_proyecto' => $request->id,  // ID del proyecto
                        'id_evento' =>  $idEvento,  // ID del evento
                        'tipo' => $request->tipo,  // Asignamos el tipo que ya tenemos
                        'subtipo' => $request->subtipo,  // Asignamos el subtipo que ya tenemos
                    ]);
                }
            } else {
                $nuevosARegistrar = array_diff($request->eventos, $hash_proyectos->pluck('id_evento')->toArray());
                // 3. Insertar solo los nuevos
                if (!empty($nuevosARegistrar)) {
                    $data = [];
                    foreach ($nuevosARegistrar as $idEvento) {
                        $data[] = [
                            'id_proyecto' => $request->id,
                            'id_evento' => $idEvento,
                            'tipo' => $request->tipo,  // Asignamos el tipo que ya tenemos
                            'subtipo' => $request->subtipo,  // Asignamos el subtipo que ya tenemos
                        ];
                    }
                    Hash_proyectos::insert($data);
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

    public function previewProyecto($id)
    {
        $proyecto = Proyectos::with('tipo_proyecto')
        ->findOrFail($id);

        // Obtener el primer evento asociado al proyecto
        $hashProyecto = Hash_proyectos::where('id_proyecto', $id)->first();
        if (!$hashProyecto) {
            abort(404, 'Proyecto no asociado a ningún evento');
        }

        $evento = Eventos::findOrFail($hashProyecto->id_evento)->nombre;

        

        return Inertia::render('Proyectos/PreviewVotacion', compact('proyecto', 'evento'));
    }
}
