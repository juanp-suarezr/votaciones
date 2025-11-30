<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Imports\VotantesFisicosImport;
use App\Models\Hash_votantes;
use App\Models\VotosDuplicados;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;

class CargueVotantesFisicoController extends Controller
{
    public function index()
    {
        $votantes = Hash_votantes::where('validaciones', 'voto fisico')
            ->with('votante')
            ->paginate(5);

        $votos_anulados = VotosDuplicados::with('votante:id,nombre,identificacion,genero')
            ->with('evento:id,nombre')
            ->paginate(5);

        return Inertia::render('CargueVotantesFisico/Index', [
            'votantes_fisicos' => $votantes,
            'votos_anulados' => $votos_anulados,
            'numRegistrosInsertados' => session('numRegistrosInsertados'),
            'numInconsistencias' => session('numInconsistencias'),
        ]);
    }

    public function cargueMasivo(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,csv,xls',
        ]);

        try {
            // Aquí deberías usar tu importador personalizado, por ejemplo VotantesFisicoImport
            $import = new VotantesFisicosImport();
            Excel::import($import, $request->file('file'));

            // Obtener el número de registros insertados correctamente
            $numRegistrosInsertados = $import->getNumRegistrosInsertados();
            $numInconsistencias = $import->getNumInconsistencias();

            // Volver a la página anterior con datos en sesión flash
            return back()
                ->with('numRegistrosInsertados', $numRegistrosInsertados)
                ->with('numInconsistencias', $numInconsistencias);
        } catch (\Exception $e) {
            return back()->with('error', 'Error en el cargue masivo: ' . $e->getMessage());
        }
    }
}
