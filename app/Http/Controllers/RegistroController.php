<?php

namespace App\Http\Controllers;

use App\Exports\RegisterExports;
use Illuminate\Support\Facades\Request as RequestFacade;
use App\Http\Controllers\Controller;
use App\Models\Puntos;
use App\Models\Registro;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;



class RegistroController extends Controller
{
    public function index()
    {

        
        
        if (auth()->check()) {

            $registro = [];
            $isValidation = false;



            if (RequestFacade::input('origin') == 'validation') {

                $isValidation = true;
                if (Auth::user()->roles[0]->name == 'Administrador') {
                    $registro =  Registro::query()
                        ->when(RequestFacade::input('search'), function ($query, $search) {
                            $query->where('nombres', 'like', '%' . $search . '%')
                                ->OrWhere('apellidos', 'like', '%' . $search . '%')
                                ->OrWhere('identificacion', 'like', '%' . $search . '%');
                        })
                        ->latest('created_at') // Ordena los resultados por la columna 'created_at' de forma descendente
                        ->first();
                } else {
                    $registro =  Registro::query()
                        ->when(RequestFacade::input('search'), function ($query, $search) {
                            $query->where('nombres', 'like', '%' . $search . '%')
                                ->OrWhere('apellidos', 'like', '%' . $search . '%')
                                ->OrWhere('identificacion', 'like', '%' . $search . '%');
                        })->whereHas('puntos_vive', function ($query) {
                            $query->where('id_user', Auth::id()); // Filtrar por el ID del usuario autenticado
                        })
                        ->latest('created_at') // Ordena los resultados por la columna 'created_at' de forma descendente
                        ->first();
                }
            } else {

                $isValidation = false;

                if (Auth::user()->roles[0]->name == 'Administrador') {
                    $registro =  Registro::query()
                        ->when(RequestFacade::input('search'), function ($query, $search) {
                            $query->where('nombres', 'like', '%' . $search . '%')
                                ->OrWhere('apellidos', 'like', '%' . $search . '%')
                                ->OrWhere('identificacion', 'like', '%' . $search . '%');
                        })->when(RequestFacade::input('fecha_init') || RequestFacade::input('fecha_end'), function ($query) {
                            $fecha_init = RequestFacade::input('fecha_init');
                            $fecha_end = RequestFacade::input('fecha_end');

                            if($fecha_end == null) {
                                $fecha_end = date('Y-m-d');
                            }

                            $query->whereBetween('created_at', [$fecha_init, $fecha_end]);
                        })
                        ->latest('created_at') // Ordena los resultados por la columna 'created_at' de forma descendente
                        ->paginate(5)
                        ->withQueryString();
                } else {
                    
                    $registro = Registro::query()

                        ->when(RequestFacade::input('search'), function ($query, $search) {
                            $query->where('nombres', 'like', '%' . $search . '%')
                                ->OrWhere('apellidos', 'like', '%' . $search . '%')
                                ->OrWhere('identificacion', 'like', '%' . $search . '%');
                        })->when(RequestFacade::input('fecha_init') || RequestFacade::input('fecha_end'), function ($query) {
                            $fecha_init = RequestFacade::input('fecha_init');
                            $fecha_end = RequestFacade::input('fecha_end');

                            if($fecha_end == null) {
                                
                                $fecha_end = date('d-m-Y');
                            }
                            
                            $query->whereBetween('created_at', [$fecha_init, $fecha_end]);
                        })->whereHas('puntos_vive', function ($query) {
                            $query->where('id_user', Auth::id()); // Filtrar por el ID del usuario autenticado
                        })
                        ->latest('created_at')
                        ->paginate(5)
                        ->withQueryString();
                }
            }



            return Inertia::render(

                'Registrados/Index',

                [
                    'registro' => $registro,
                    'filters' => RequestFacade::only(['search', 'fecha_init', 'fecha_end']),
                    'validation' => $isValidation,
                    'puntos' => Puntos::all(),
                ]
            );
        } else {
            return redirect()->route('login', ['origin' => 'validation', 'identificacion' => RequestFacade::input('search')]);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {

        $puntos = Puntos::where('code', $request->codePunto)->get();

        $request->validate([
            'nombres' => 'required|string|max:100',
            'apellidos' => 'required|string|max:100',
            'edad' => 'required',
            'tipo_documento' => 'required',
            'identificacion' => 'required|max:20',
            'direccion' => 'required|string|max:100',
            'sector' => 'required',
            'telefono' => 'required|max:15',
            'email' => 'required|string|lowercase|email|max:100',
            'genero' => 'required',
            'condicion' => 'required',
            'etnia' => 'required',
            'nivel_estudio' => 'required',
            'codePunto' => 'required',

        ]);

        $user = Registro::create([
            'nombres' => $request->nombres,
            'apellidos' => $request->apellidos,
            'edad' => $request->edad,
            'tipo_documento' => $request->tipo_documento,
            'identificacion' => $request->identificacion,
            'direccion' => $request->direccion,
            'sector' => $request->sector,
            'telefono' => $request->telefono,
            'email' => $request->email,
            'genero' => $request->genero,
            'condicion' => $request->condicion,
            'etnia' => $request->etnia,
            'nivel_estudio' => $request->nivel_estudio,
            'id_puntos' => $puntos[0]->id,

        ]);

        event(new Registered($user));

        // $fechaCreacion = $user->created_at;
        // dd($fechaCreacion->format('Y-m-d H:i:s')); "2024-04-07 16:40:17"



        return redirect()->route('register');
        // return Redirect::route('register')->with('fechaCreacion', $fechaCreacion->format('Y-m-d H:i:s'));
    }

    public function edit(Request $request)
    {


        return Inertia::render(

            'Registrados/Edit',


            [
                'usuario' => Registro::findOrFail($request->id),
            ]
        );
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'nombres' => 'required|string|max:100',
            'apellidos' => 'required|string|max:100',
            'edad' => 'required',
            'tipo_documento' => 'required',
            'identificacion' => 'required|max:20',
            'direccion' => 'required|string|max:100',
            'sector' => 'required',
            'telefono' => 'required|max:15',
            'email' => 'required|string|lowercase|email|max:100',
            'genero' => 'required',
            'condicion' => 'required',
            'etnia' => 'required',
            'nivel_estudio' => 'required'
        ]);
        // Actualizar el modelo de usuario con los datos validados

        $user = Registro::findOrFail($request->id);
        $user->fill($data)->save();
        return redirect()->route('registrados.index');
    }

    public function excel()
    {
        ob_end_clean();
        ob_start();

        $startDate = RequestFacade::input('fecha_init');
        $endDate = RequestFacade::input('fecha_end');

        return Excel::download(new RegisterExports($startDate, $endDate), 'register.xls', \Maatwebsite\Excel\Excel::XLS);
    }
}
