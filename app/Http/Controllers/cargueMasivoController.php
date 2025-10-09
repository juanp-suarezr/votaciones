<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\VotersImport;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Illuminate\Support\Facades\Request as RequestFacade;
use App\Exports\JuradosExports;
use App\Imports\JuradosImport;
use App\Imports\ParametrosDetalleImport;

class cargueMasivoController extends Controller
{

    //PLANTILLA EXCEL USUARIOS
    public function plantillaRes()
    {
        $filePath = 'excel/votantes.xls'; // Ruta del archivo Excel almacenado

        return Storage::download($filePath); // Devuelve el archivo para descarga
    }

    public function cargueVotantes(Request $request)
    {
        $request->validate([
            'votantes' => 'required|file',
            'eventos' => 'required|exists:eventos,id',
        ]);



        // Realizar la importación
        $import = new VotersImport($request->eventos, $request->tipo); // Instancia correcta de VotersImport
        Excel::import($import, $request->file('votantes'));

        // Obtener el número de registros insertados correctamente
        $numRegistrosInsertados = $import->getNumRegistrosInsertados();
        $numRegistrosActualizados = $import->getNumRegistrosActualizados();

        return Inertia::render('Users/Add', [
            'numRegistrosInsertados' => $numRegistrosInsertados,
            'numRegistrosActualizados' => $numRegistrosActualizados,

        ]);
    }

    //PLANTILLA EXCEL JURADOS
    public function plantillaJur()
    {
        ob_end_clean();
        ob_start();

        $id_evento = 15;


        $id_evento = RequestFacade::input('id_evento');



        return Excel::download(new JuradosExports($id_evento), 'jurados.xls', \Maatwebsite\Excel\Excel::XLS);
    }

    public function cargueJurados(Request $request)
    {
        $request->validate([
            'jurados' => 'required|file',
            'id_evento' => 'required|exists:eventos,id',
        ]);



        // Realizar la importación
        $import = new JuradosImport($request->id_evento); // Instancia correcta de VotersImport
        Excel::import($import, $request->file('jurados'));

        // Obtener el número de registros insertados correctamente
        $numRegistrosInsertados = $import->getNumRegistrosInsertados();
        $numRegistrosActualizados = $import->getNumRegistrosActualizados();

        return Inertia::render('Auth/RegistroJurados', [
            'numRegistrosInsertados' => $numRegistrosInsertados,
            'numRegistrosActualizados' => $numRegistrosActualizados,

        ]);
    }

    //CARGUE MASIVO PARAMETROS DETALLE
    public function cargueParametrosDetalle(Request $request)
    {
        $request->validate([
            'file' => 'required|file',
            'codParametro' => 'required',
        ]);



        // Realizar la importación
        $import = new ParametrosDetalleImport($request->codParametro); // Instancia correcta de VotersImport
        Excel::import($import, $request->file('file'));

        // Obtener el número de registros insertados correctamente
        $numRegistrosInsertados = $import->getNumRegistrosInsertados();


        return back()->with([
        'numRegistrosInsertados' => $numRegistrosInsertados,
    ]);
    }

}
