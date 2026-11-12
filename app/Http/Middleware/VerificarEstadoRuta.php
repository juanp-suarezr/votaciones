<?php

namespace App\Http\Middleware;

use App\Models\RutasVotaciones;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Inertia\Inertia;

class VerificarEstadoRuta
{
    /**
     * Verifica si la ruta solicitada está bloqueada en la tabla RutasVotaciones.
     * Soporta coincidencia con o sin diagonal inicial ('/'), nombres de ruta,
     * estado numérico (0) o textual ('Bloqueado'), y comprobación dinámica de fechas.
     */
    public function handle(Request $request, Closure $next)
    {
        // 1. Rutas del sistema que NUNCA deben ser bloqueadas para evitar bloqueos accidentales a administradores
        $excluidas = [
            'login',
            'logout',
            'dashboard',
            'rutas*',
            'users*',
            'roles*',
            'parametros*',
            'delegados*',
            'actas-comision*',
            'actas-generales*',
            'mi-firma-digital*',
            'sanctum/*',
            'api/*',
        ];

        foreach ($excluidas as $patron) {
            if ($request->is($patron)) {
                return $next($request);
            }
        }

        // 2. Normalizar ruta actual
        $rawPath = $request->path(); // Ej: "inscripcion-candidatos"
        $cleanPath = trim($rawPath, '/');
        $routeName = $request->route() ? $request->route()->getName() : null;

        // 3. Buscar coincidencia en RutasVotaciones
        $ruta = RutasVotaciones::where(function ($q) use ($rawPath, $cleanPath, $routeName) {
            $q->where('path', $cleanPath)
              ->orWhere('path', '/' . $cleanPath)
              ->orWhere('path', $rawPath);
            if ($routeName) {
                $q->orWhere('path', $routeName);
            }
        })->first();

        if ($ruta) {
            $isBloqueado = false;

            // Comprobación de estado (numérico 0 o cadena 'bloqueado', '0', 'inactivo', etc.)
            $estadoStr = strtolower(trim((string)$ruta->estado));
            if ($estadoStr === '0' || $estadoStr === 'bloqueado' || $estadoStr === 'inactivo' || $estadoStr === 'cerrado' || $estadoStr === 'false') {
                $isBloqueado = true;
            }

            // Comprobación dinámica de fechas si no está explícitamente bloqueado
            if (!$isBloqueado && ($ruta->fecha_inicio || $ruta->fecha_fin)) {
                $ahora = Carbon::now();
                if ($ruta->fecha_inicio && $ruta->fecha_fin) {
                    if (!$ahora->between($ruta->fecha_inicio, $ruta->fecha_fin)) {
                        $isBloqueado = true;
                    }
                } elseif ($ruta->fecha_inicio && $ahora->lessThan($ruta->fecha_inicio)) {
                    $isBloqueado = true;
                } elseif ($ruta->fecha_fin && $ahora->greaterThan($ruta->fecha_fin)) {
                    $isBloqueado = true;
                }
            }

            // Si está bloqueada, responder con la vista oficial protegida
            if ($isBloqueado) {
                $titulo = 'Acceso Temporalmente Deshabilitado';
                $mensaje = 'Esta sección se encuentra temporalmente inactiva de conformidad con el cronograma del proceso electoral. Por favor intente más tarde o consulte con el administrador del sistema.';

                // Respuesta si la petición espera JSON
                if ($request->expectsJson() && !$request->header('X-Inertia')) {
                    return response()->json([
                        'error' => $titulo,
                        'mensaje' => $mensaje,
                        'bloqueado' => true,
                    ], 403);
                }

                // Respuesta si la petición proviene de Inertia SPA
                if ($request->header('X-Inertia')) {
                    return Inertia::render('Errors/RutaBloqueada', [
                        'titulo' => $titulo,
                        'mensaje' => $mensaje,
                        'ruta' => $ruta,
                    ])->toResponse($request)->setStatusCode(403);
                }

                // Respuesta estándar HTML Blade (autocontenida y con diseño oficial)
                return response()->view('errors.ruta-bloqueada', [
                    'titulo' => $titulo,
                    'mensaje' => $mensaje,
                    'ruta' => $ruta,
                ], 403);
            }
        }

        return $next($request);
    }
}

