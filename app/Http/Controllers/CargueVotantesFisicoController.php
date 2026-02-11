<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Imports\VotantesFisicosImport;
use App\Imports\ActualizarInformacionVotantesImport;
use App\Mail\InformeActualizacionVotantesMail;
use App\Models\Hash_votantes;
use App\Models\User;
use App\Models\VotosDuplicados;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
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

    /**
     * Muestra la página para cargar información de votantes
     */
    public function paginaCargarInformacion()
    {
        return Inertia::render('CargueVotantesFisico/CargarInformacion', [
            'numRegistrosActualizados' => session('numRegistrosActualizados'),
            'numNoEncontrados' => session('numNoEncontrados'),
            'numErrores' => session('numErrores'),
            'erroresDetallados' => session('erroresDetallados'),
        ]);
    }

    /**
     * Procesa el archivo Excel para actualizar la información de los votantes
     */
    public function cargarInformacion(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,csv,xls',
        ]);

        try {
            $import = new ActualizarInformacionVotantesImport();
            Excel::import($import, $request->file('file'));

            $numRegistrosActualizados = $import->getNumRegistrosActualizados();
            $numNoEncontrados = $import->getNumNoEncontrados();
            $numErrores = $import->getNumErrores();
            $erroresDetallados = $import->getErroresDetallados();
            $votantesActualizados = $import->getVotantesActualizados();
            $votantesNoActualizados = $import->getVotantesNoActualizados();
            $totalRegistros = $import->getTotalRegistros();

            // Enviar correo con el informe
            $this->enviarInformePorCorreo([
                'totalRegistros' => $totalRegistros,
                'actualizados' => $numRegistrosActualizados,
                'noEncontrados' => $numNoEncontrados,
                'errores' => $numErrores,
                'votantesActualizados' => $votantesActualizados,
                'votantesNoActualizados' => $votantesNoActualizados,
                'fechaProceso' => now()->format('d/m/Y H:i:s'),
            ]);

            if ($request->ajax() || $request->wantsJson() || $request->header('X-Inertia') === null) {
                return response()->json([
                    'numRegistrosActualizados' => $numRegistrosActualizados,
                    'numNoEncontrados' => $numNoEncontrados,
                    'numErrores' => $numErrores,
                    'erroresDetallados' => $erroresDetallados,
                ]);
            }

            return back()
                ->with('numRegistrosActualizados', $numRegistrosActualizados)
                ->with('numNoEncontrados', $numNoEncontrados)
                ->with('numErrores', $numErrores)
                ->with('erroresDetallados', $erroresDetallados);
        } catch (\Exception $e) {
            Log::error('Error en cargue de información: ' . $e->getMessage());
            return back()->with('error', 'Error en el cargue de información: ' . $e->getMessage());
        }
    }

    /**
     * Envía el informe de actualización por correo electrónico
     */
    private function enviarInformePorCorreo($datos)
    {
        try {
            // Obtener correos de destinatarios (configurables o por defecto)
            $destinatarios = $this->obtenerDestinatarios();

            if (empty($destinatarios)) {
                Log::warning('No se encontraron destinatarios para enviar el informe de actualización de votantes');
                return;
            }

            $correo = new InformeActualizacionVotantesMail($datos);

            foreach ($destinatarios as $email) {
                Mail::to($email)->queue($correo);
            }

            Log::info('Informe de actualización de votantes enviado a: ' . implode(', ', $destinatarios));
        } catch (\Exception $e) {
            Log::error('Error al enviar informe por correo: ' . $e->getMessage());
        }
    }

    /**
     * Obtiene los destinatarios para el informe
     * Por defecto usa el correo del administrador o configura en .env
     */
    private function obtenerDestinatarios()
    {
        // Opción 1: Verificar variable de entorno
        if (env('EMAIL_INFORME_VOTANTES')) {
            return array_map('trim', explode(',', env('EMAIL_INFORME_VOTANTES')));
        }

        // Opción 2: Buscar administradores
        $admins = User::role(['admin', 'superadmin'])->pluck('email')->toArray();

        if (!empty($admins)) {
            return $admins;
        }

        // Opción 3: Buscar el primer usuario con email verificado
        $admin = User::whereNotNull('email_verified_at')
            ->orderBy('id')
            ->first();

        if ($admin && $admin->email) {
            return [$admin->email];
        }

        return [];
    }

    public function cargueMasivo(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,csv,xls',
        ]);

        try {
            $import = new VotantesFisicosImport();
            Excel::import($import, $request->file('file'));

            $numRegistrosInsertados = $import->getNumRegistrosInsertados();
            $numInconsistencias = $import->getNumInconsistencias();

            if ($request->ajax() || $request->wantsJson() || $request->header('X-Inertia') === null) {
                return response()->json([
                    'numRegistrosInsertados' => $numRegistrosInsertados,
                    'numInconsistencias' => $numInconsistencias,
                ]);
            }

            return back()
                ->with('numRegistrosInsertados', $numRegistrosInsertados)
                ->with('numInconsistencias', $numInconsistencias);
        } catch (\Exception $e) {
            return back()->with('error', 'Error en el cargue masivo: ' . $e->getMessage());
        }
    }
}
