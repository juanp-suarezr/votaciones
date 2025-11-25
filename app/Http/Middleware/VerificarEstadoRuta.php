<?php

namespace App\Http\Middleware;

use App\Models\RutasVotaciones;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class VerificarEstadoRuta
{
    public function handle(Request $request, Closure $next)
    {
        // Obtener la ruta actual (por URL o nombre)
        $rutaActual = $request->path(); // ejemplo: "votaciones/publica"
        // Si usas nombres de ruta, podrías usar:
        // $rutaActual = $request->route()->getName();

        // Buscar si la ruta está registrada en la base de datos
        $ruta = RutasVotaciones::where('path', $rutaActual)->first();
        
        if ($ruta) {

            // Suponiendo que el campo estado = 1 es activo y 0 es bloqueado
            if ($ruta->estado == 0) {
                return response()->view('errors.ruta-bloqueada', [
                    'titulo' => 'Ruta temporalmente bloqueada',
                    'mensaje' => 'Esta ruta se encuentra deshabilitada temporalmente. Por favor, intente más tarde.'
                ], 403);
            }
        } else {
            Log::warning('Ruta no encontrada en la tabla RutasVotaciones', ['ruta' => $rutaActual]);
        }

        return $next($request);
    }
}
