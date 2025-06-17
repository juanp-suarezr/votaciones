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


class ParametrosController extends Controller

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


        return Inertia::render(
            'Parametros/Index',
            [
                'parametros' => Parametros::query()
                    ->when(RequestFacade::input('search'), function ($query, $search) {
                        $query->where('detalle', 'like', '%' . $search . '%')
                            ->orWhere('cod', $search);
                    })
                    ->when($estado !== null, function ($query) use ($estado) {
                        $query->where('estado', $estado);
                    })
                    ->withCount('parametrosDetalle')
                    ->paginate(5)
                    ->withQueryString(),


                'filters' => RequestFacade::only(['search', 'estado']),

            ]
        );
    }

    public function create()
    {

        return Inertia::render('Parametros/Add', [
            'codigos' => Parametros::select('cod')->get(),

        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'cod' => 'required|string|unique:' . Parametros::class,
            'detalle' => 'required|string|max:100',
            'estado' => 'required',

        ]);

        DB::beginTransaction(); // Iniciar la transacción

        try {
            // Crear el usuario
            $parametro = Parametros::create([
                'cod' => $request->cod,
                'detalle' => $request->detalle,
                'estado' => $request->estado,
            ]);

            //save
            $parametro->save();


            // Confirmar la transacción
            DB::commit();


            return back()->with('success', 'Parametro creado exitosamente');
        } catch (\Exception $e) {
            // Si ocurre algún error, revertir todos los cambios
            DB::rollBack();

            // Puedes optar por lanzar el error o retornar un mensaje de error
            return redirect()->back()->withErrors(['error' => 'Error al crear el parametro: ' . $e->getMessage()]);
        }
    }


    public function edit(Request $request, $id_parametro)
    {


        return Inertia::render('Parametros/Edit', [
            'parametro' => Parametros::where('id', $id_parametro)->first(),
            'codigos' => Parametros::select('cod')->get(),

        ]);
    }

    public function update(Request $request, Parametros $parametro)
    {
        
        $request->validate([
            'cod' => ['required', 'string', Rule::unique('parametros')->ignore($parametro)],
            'detalle' => 'required|string|max:100',
            'estado' => 'required',

        ]);

        DB::beginTransaction
        (); // Iniciar la transacción

        try {
            // Crear el usuario
            $parametro->update([
                'cod' => $request->cod,
                'detalle' => $request->detalle,
                'estado' => $request->estado,
            ]);

            //save
            $parametro->save();

            // Confirmar la transacción
            DB::commit();

            return back()
                ->with('success', 'Parametro actualizado exitosamente');
        }

        catch (\Exception $e) {
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
