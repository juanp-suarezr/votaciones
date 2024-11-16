<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\VotersImport;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class cargueMasivoController extends Controller
{

    //PLANTILLA EXCEL RESTAURANTES
    public function plantillaRes()
    {
        $filePath = 'excel/votantes.xlsx'; // Ruta del archivo Excel almacenado

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
}
