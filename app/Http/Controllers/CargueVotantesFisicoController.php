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
            ->paginate(5, ['*'], 'votantes_page');

        $votos_anulados = VotosDuplicados::with('votante:id,nombre,identificacion,genero,comuna')
            ->with('evento:id,nombre')
            ->paginate(5, ['*'], 'anulados_page');

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

            // Si la petición es AJAX/JSON, responder con JSON (evita redirect que fuerza reload)
            if ($request->ajax() || $request->wantsJson() || $request->header('X-Inertia') === null) {
                return response()->json([
                    'numRegistrosInsertados' => $numRegistrosInsertados,
                    'numInconsistencias' => $numInconsistencias,
                ]);
            }

            // De lo contrario, volver a la página anterior con datos en sesión flash
            return back()
                ->with('numRegistrosInsertados', $numRegistrosInsertados)
                ->with('numInconsistencias', $numInconsistencias);
        } catch (\Exception $e) {
            return back()->with('error', 'Error en el cargue masivo: ' . $e->getMessage());
        }
    }
}
