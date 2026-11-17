<?php

namespace App\Http\Controllers;

use App\Jobs\EnviarResultadosPpJob;
use App\Models\Eventos;
use App\Models\PpHomologacionComuna;
use App\Models\PpIntegracionLog;
use App\Models\Proyectos;
use App\Models\Votos;
use App\Services\GreenPpService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class PpIntegracionController extends Controller
{
    protected GreenPpService $greenService;

    public function __construct(GreenPpService $greenService)
    {
        $this->greenService = $greenService;
    }

    /**
     * Panel de control de la integración con Presupuesto Participativo
     */
    public function index(Request $request)
    {
        // 1. Consultar convocatorias disponibles en GREEN
        $resultadoConvocatorias = $this->greenService->listarConvocatorias();
        $convocatorias = $resultadoConvocatorias['ok'] ? $resultadoConvocatorias['data'] : [];

        // 2. Proyectos sincronizados en la BD local
        $proyectosSincronizados = Proyectos::whereNotNull('id_postulacion_green')
            ->with(['parametroDetalle', 'tipo_proyecto'])
            ->orderBy('id', 'desc')
            ->paginate(10, ['*'], 'page_proyectos');

        // 3. Logs de auditoría
        $logs = PpIntegracionLog::with(['evento:id,nombre', 'proyecto:id,detalle'])
            ->orderBy('id', 'desc')
            ->paginate(15, ['*'], 'page_logs');

        // 4. Homologaciones territoriales
        $homologaciones = PpHomologacionComuna::with('comunaVota')
            ->orderBy('green_comuna_id', 'asc')
            ->get();

        // 5. Eventos de presupuesto participativo disponibles
        $eventosPadre = Eventos::whereNull('evento_padre')
            ->where('nombre', 'like', '%Presupuesto%')
            ->orderBy('id', 'desc')
            ->get(['id', 'nombre', 'estado']);

        return Inertia::render('IntegracionPP/Index', [
            'conexion_ok' => $resultadoConvocatorias['ok'],
            'conexion_error' => $resultadoConvocatorias['error'] ?? null,
            'convocatorias' => $convocatorias,
            'proyectos_sincronizados' => $proyectosSincronizados,
            'logs' => $logs,
            'homologaciones' => $homologaciones,
            'eventos_padre' => $eventosPadre,
            'config' => [
                'base_url' => config('services.green_pp.base_url'),
                'token_resumen' => substr(config('services.green_pp.token'), 0, 10) . '...',
            ],
        ]);
    }

    /**
     * Disparador manual para sincronizar una convocatoria de GREEN
     */
    public function sincronizar(Request $request)
    {
        $request->validate([
            'id_convocatoria' => 'required|integer',
            'id_evento_padre' => 'nullable|integer',
        ]);

        $res = $this->greenService->sincronizarConvocatoria(
            (int)$request->id_convocatoria,
            $request->id_evento_padre ? (int)$request->id_evento_padre : null
        );

        if ($res['ok']) {
            return redirect()->back()->with('success', "Convocatoria #{$res['convocatoria_id']} sincronizada con éxito: {$res['proyectos_creados']} proyectos creados, {$res['proyectos_actualizados']} actualizados.");
        }

        return redirect()->back()->withErrors(['error' => 'Error sincronizando con GREEN: ' . ($res['error'] ?? 'Error desconocido')]);
    }

    /**
     * Consolida y envía proyectos ganadores de un evento a GREEN
     */
    public function enviarResultados(Request $request)
    {
        $request->validate([
            'id_evento' => 'required|integer',
            'id_convocatoria' => 'required|integer',
            'top_ganadores' => 'nullable|integer|min:1|max:10',
        ]);

        $idEvento = (int)$request->id_evento;
        $idConvocatoria = (int)$request->id_convocatoria;
        $top = (int)($request->top_ganadores ?? 1);

        // 1. Calcular votos por proyecto en este evento
        $conteoVotos = Votos::where('id_eventos', $idEvento)
            ->whereNotNull('id_proyecto')
            ->select('id_proyecto', DB::raw('count(*) as total_votos'))
            ->groupBy('id_proyecto')
            ->orderByDesc('total_votos')
            ->take($top)
            ->get();

        if ($conteoVotos->isEmpty()) {
            return redirect()->back()->withErrors(['error' => 'No se registran votos para este evento o no hay proyectos participantes.']);
        }

        // 2. Extraer identificadores originales de GREEN
        $ganadores = [];
        foreach ($conteoVotos as $pos => $voto) {
            $proyecto = Proyectos::find($voto->id_proyecto);
            if ($proyecto && $proyecto->id_postulacion_green) {
                $ganadores[] = [
                    'id_postulacion_green' => $proyecto->id_postulacion_green,
                    'id_proyecto_local' => $proyecto->id,
                    'nombre' => $proyecto->detalle,
                    'total_votos' => $voto->total_votos,
                    'posicion' => $pos + 1,
                ];
            }
        }

        if (empty($ganadores)) {
            return redirect()->back()->withErrors(['error' => 'Los proyectos más votados no tienen un ID original de GREEN (id_postulacion_green). Asegúrese de haberlos importado desde la API.']);
        }

        // 3. Despachar Job con reintentos
        EnviarResultadosPpJob::dispatch($idConvocatoria, $ganadores, $idEvento);

        return redirect()->back()->with('success', 'Envío de resultados encolado exitosamente con reintentos automáticos. Verifique el estado en el registro de auditoría.');
    }

    /**
     * Reintenta un despacho de resultados que haya fallado
     */
    public function reintentar(Request $request, $idLog)
    {
        $log = PpIntegracionLog::findOrFail($idLog);

        if ($log->tipo !== 'OUTBOUND_RESULTADOS' || empty($log->payload_enviado)) {
            return redirect()->back()->withErrors(['error' => 'Solo se pueden reintentar despachos salientes con payload registrado.']);
        }

        $payload = $log->payload_enviado;
        $idConvocatoria = $log->id_convocatoria_green ?? ($payload['id_convocatoria'] ?? null);
        $ganadores = $payload['ganadores'] ?? [];

        if (!$idConvocatoria || empty($ganadores)) {
            return redirect()->back()->withErrors(['error' => 'El log no contiene los datos requeridos para reintentar.']);
        }

        $log->estado = 'REINTENTANDO';
        $log->save();

        EnviarResultadosPpJob::dispatch((int)$idConvocatoria, $ganadores, $log->id_evento_vota, $log->id);

        return redirect()->back()->with('success', 'Reintento lanzado inmediatamente.');
    }

    /**
     * Endpoint Webhook para recibir notificaciones automáticas de GREEN (Producción)
     * POST /api/v1/integracion/green-pp/webhook
     */
    public function webhook(Request $request)
    {
        // 1. Validar autenticación/firma
        $secretEsperado = config('services.green_pp.webhook_secret');
        $signatureRecibida = $request->header('X-Hub-Signature-256') ?? $request->header('X-Webhook-Secret') ?? $request->bearerToken();

        if ($secretEsperado && $signatureRecibida !== $secretEsperado) {
            Log::warning('Webhook GREEN PP: intento de acceso no autorizado', ['ip' => $request->ip()]);
            return response()->json(['message' => 'No autorizado'], 401);
        }

        $evento = $request->input('evento') ?? $request->input('event');
        $idConvocatoria = $request->input('id_convocatoria') ?? $request->input('convocatoria_id');

        Log::info("Webhook GREEN PP recibido", ['evento' => $evento, 'id_convocatoria' => $idConvocatoria]);

        // Registrar log de recepción
        PpIntegracionLog::create([
            'tipo' => 'WEBHOOK',
            'id_convocatoria_green' => $idConvocatoria,
            'endpoint' => '/api/v1/integracion/green-pp/webhook',
            'metodo' => 'POST',
            'payload_enviado' => $request->all(),
            'respuesta_recibida' => json_encode(['recibido' => true]),
            'http_status' => 200,
            'estado' => 'EXITOSO',
        ]);

        // Si la notificación indica que hay proyectos viables o convocatoria abierta, sincronizar
        if ($idConvocatoria) {
            $this->greenService->sincronizarConvocatoria((int)$idConvocatoria);
        }

        return response()->json([
            'ok' => true,
            'mensaje' => 'Webhook procesado exitosamente',
            'convocatoria_id' => $idConvocatoria,
        ], 200);
    }
}

