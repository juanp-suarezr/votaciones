<?php

namespace App\Http\Controllers;

use App\Models\Eventos;
use App\Models\Hash_votantes;
use App\Models\Informacion_votantes;
use App\Models\Parametros;
use App\Models\ParametrosDetalle;
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
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ParametrosDetalleController extends Controller

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

        $estado = RequestFacade::input('estado');


        $parametros = ParametrosDetalle::query()

            ->when(RequestFacade::input('search'), function ($query, $search) {
                $query->where('detalle', 'like', '%' . $search . '%');
            })
            ->when($estado !== null, function ($query) use ($estado) {
                $query->where('estado', $estado);
            })
            ->where('codParametro', RequestFacade::input('codParametro'))
            ->paginate(5)
            ->withQueryString();


        return Inertia::render(
            'Parametros/ParametrosDetalle/Index',
            [
                'parametros' => $parametros,


                'filters' => RequestFacade::only(['search', 'estado', 'codParametro']),

            ]
        );
    }


    public function store(Request $request)
    {
        $request->validate([
            'detalle' => 'required|string|max:100',
            'estado' => '',

        ]);

        DB::beginTransaction(); // Iniciar la transacción

        try {
            // Crear el usuario
            $parametro = ParametrosDetalle::create([
                'codParametro' => $request->codParametro,
                'detalle' => $request->detalle,
                'estado' => 1,
            ]);

            //save
            $parametro->save();


            // Confirmar la transacción
            DB::commit();


            return back()->with('success', 'Parametro creado exitosamente');
        } catch (\Exception $e) {
            // Si ocurre algún error, revertir todos los cambios
            DB::rollBack();
            Log::error($e->getMessage());

            // Puedes optar por lanzar el error o retornar un mensaje de error
            return redirect()->back()->withErrors(['error' => 'Error al crear el parametro: ' . $e->getMessage()]);
        }
    }


    public function update(Request $request, ParametrosDetalle $parametrosDetalle)
    {

        $request->validate([
            'detalle' => 'required|string|max:100',
            'estado' => '',

        ]);


        DB::beginTransaction(); // Iniciar la transacción

        try {
            // Actualizar el parámetroDetalle
            $parametrosDetalle->update($request->only(['detalle', 'estado']));

            // Confirmar la transacción
            DB::commit();

            return back()
                ->with('success', 'Parametro actualizado exitosamente');
        } catch (\Exception $e) {
            // Si ocurre algún error, revertir todos los cambios
            DB::rollBack();
            Log::error($e->getMessage());
            // Puedes optar por lanzar el error o retornar un mensaje de error
            return redirect()->back()->withErrors(['error' => 'Error al actualizar el parámetro: ' . $e->getMessage()]);
        }
    }

    public function destroy(Request $request, $id)
    {

        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $parametro = ParametrosDetalle::find($id);
        $parametro->delete();
        

        return back()
            ->with('success', 'Recurso eliminado exitosamente');
    }
}
