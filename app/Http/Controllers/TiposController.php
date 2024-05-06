<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Tipos;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class TiposController extends Controller

{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    function __construct()
    {
        $this->middleware('permission:tipos-listar|tipos-crear|tipos-editar|tipos-eliminar', ['only' => ['index', 'store']]);
        $this->middleware('permission:tipos-crear', ['only' => ['create', 'store']]);
        $this->middleware('permission:tipos-editar', ['only' => ['edit', 'update']]);
        $this->middleware('permission:tipos-eliminar', ['only' => ['destroy']]);
    }

    public function index() {

        return Inertia::render(

            'Tipos/Index',

            [
                'tipos' => Tipos::get(),
            ]
        );

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'nombre' => 'required|string|max:100',
        ]);

        $tipos = Tipos::create(['nombre' => $request->input('nombre')]);
        $tipos->save();
        
        return back();
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'nombre' => 'required|string|max:100',
        ]);

        $tipos = Tipos::find($id);
        $tipos->nombre = $request->nombre;
        $tipos->save();

        return back();
    }

    public function destroy(Request $request,$id){

        
        DB::table("tipos")->where('id', $id)->delete();
        return back();
    }

}
