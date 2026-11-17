<?php

namespace App\Services;

use App\Models\Eventos;
use App\Models\Hash_eventos_hijos;
use App\Models\Hash_proyectos;
use App\Models\ParametrosDetalle;
use App\Models\PpHomologacionComuna;
use App\Models\PpIntegracionLog;
use App\Models\Proyectos;
use App\Models\Tipo_proyectos;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class GreenPpService
{
    protected string $baseUrl;
    protected string $token;
    protected int $timeout;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.green_pp.base_url', 'http://localhost/api/v1/pp'), '/');
        $this->token = config('services.green_pp.token', '1|hrpLFod7R2ppecAdGgN9d1LntnAn9PMDjRycMR2353377cdd');
        $this->timeout = (int)config('services.green_pp.timeout', 15);
    }

    /**
     * Cliente HTTP base configurado con Bearer Token y cabeceras
     */
    protected function client()
    {
        return Http::withToken($this->token)
            ->timeout($this->timeout)
            ->acceptJson();
    }

    /**
     * 1. Consulta la lista de convocatorias de Presupuesto Participativo
     * GET /api/v1/pp/convocatorias
     */
    public function listarConvocatorias(array $filtros = []): array
    {
        $endpoint = "{$this->baseUrl}/convocatorias";
        $params = array_filter([
            'estado' => $filtros['estado'] ?? null,
            'tipo' => $filtros['tipo'] ?? null,
            'comuna' => $filtros['comuna'] ?? null,
        ]);

        try {
            $response = $this->client()->get($endpoint, $params);

            $this->registrarLog([
                'tipo' => 'INBOUND_SYNC',
                'endpoint' => $endpoint,
                'metodo' => 'GET',
                'payload_enviado' => $params,
                'respuesta_recibida' => $response->body(),
                'http_status' => $response->status(),
                'estado' => $response->successful() ? 'EXITOSO' : 'FALLIDO',
                'ultimo_error' => $response->successful() ? null : ($response->json('message') ?? 'Error consultando convocatorias'),
            ]);

            if ($response->successful()) {
                return [
                    'ok' => true,
                    'data' => $response->json('data', []),
                ];
            }

            return [
                'ok' => false,
                'error' => $response->json('message') ?? "Error HTTP {$response->status()}",
                'data' => [],
            ];
        } catch (\Throwable $th) {
            $this->registrarLog([
                'tipo' => 'INBOUND_SYNC',
                'endpoint' => $endpoint,
                'metodo' => 'GET',
                'payload_enviado' => $params,
                'http_status' => 0,
                'estado' => 'FALLIDO',
                'ultimo_error' => $th->getMessage(),
            ]);

            Log::error("GreenPpService: error conectando con {$endpoint}", ['error' => $th->getMessage()]);

            return [
                'ok' => false,
                'error' => $th->getMessage(),
                'data' => [],
            ];
        }
    }

    /**
     * 2. Consulta el detalle de una convocatoria y todas sus postulaciones
     * GET /api/v1/pp/convocatorias/{id}
     */
    public function obtenerConvocatoria(int $idConvocatoria): array
    {
        $endpoint = "{$this->baseUrl}/convocatorias/{$idConvocatoria}";

        try {
            $response = $this->client()->get($endpoint);

            $this->registrarLog([
                'tipo' => 'INBOUND_SYNC',
                'id_convocatoria_green' => $idConvocatoria,
                'endpoint' => $endpoint,
                'metodo' => 'GET',
                'respuesta_recibida' => $response->body(),
                'http_status' => $response->status(),
                'estado' => $response->successful() ? 'EXITOSO' : 'FALLIDO',
                'ultimo_error' => $response->successful() ? null : ($response->json('message') ?? 'Error obteniendo detalle'),
            ]);

            if ($response->successful()) {
                return [
                    'ok' => true,
                    'data' => $response->json('data', []),
                ];
            }

            return [
                'ok' => false,
                'error' => $response->json('message') ?? "Error HTTP {$response->status()}",
                'data' => null,
            ];
        } catch (\Throwable $th) {
            $this->registrarLog([
                'tipo' => 'INBOUND_SYNC',
                'id_convocatoria_green' => $idConvocatoria,
                'endpoint' => $endpoint,
                'metodo' => 'GET',
                'http_status' => 0,
                'estado' => 'FALLIDO',
                'ultimo_error' => $th->getMessage(),
            ]);

            return [
                'ok' => false,
                'error' => $th->getMessage(),
                'data' => null,
            ];
        }
    }

    /**
     * 3. Sincroniza e importa una convocatoria de GREEN a la base de datos local de VOTA YA.
     * Homologa comunas, crea/asocia evento padre e hijos, y registra proyectos con id_postulacion_green.
     */
    public function sincronizarConvocatoria(int $idConvocatoria, ?int $idEventoPadre = null): array
    {
        $res = $this->obtenerConvocatoria($idConvocatoria);

        if (!$res['ok'] || empty($res['data'])) {
            return [
                'ok' => false,
                'error' => $res['error'] ?? 'No se pudo obtener la información de la convocatoria desde GREEN.',
            ];
        }

        $data = $res['data'];
        $comunasGreen = $data['comunas'] ?? [];
        $postulaciones = $data['postulaciones'] ?? [];

        // 1. Homologar las comunas de la convocatoria
        $comunasHomologadas = $this->homologarComunas($comunasGreen);

        // 2. Resolver o crear el Evento Padre de Presupuesto Participativo
        $eventoPadre = $this->resolverEventoPadre($data, $idEventoPadre);

        // 3. Resolver o crear los Eventos Hijos (uno por comuna/corregimiento)
        $eventosHijosPorComuna = $this->resolverEventosHijos($eventoPadre, $comunasHomologadas);

        // 4. Importar o actualizar las postulaciones como proyectos locales
        $proyectosCreados = 0;
        $proyectosActualizados = 0;

        // Tipo de proyecto predeterminado (Presupuesto Participativo)
        $tipoProyecto = Tipo_proyectos::firstOrCreate(
            ['nombre' => 'Presupuesto Participativo'],
            ['nombre' => 'Presupuesto Participativo']
        );

        // Determinar comuna principal o distribuir entre las comunas de la convocatoria
        $comunaDefaultId = !empty($comunasHomologadas) ? reset($comunasHomologadas)['vota_id'] : null;

        foreach ($postulaciones as $idx => $post) {
            $idPostulacionGreen = $post['id'] ?? null;
            if (!$idPostulacionGreen) continue;

            $estadoGreen = is_array($post['estado'] ?? null)
                ? ($post['estado']['codigo'] ?? 'EN_REVISION')
                : ($post['estado'] ?? 'EN_REVISION');

            // Nombre y descripción: en la v1 de GREEN vienen en null
            $nombreProyecto = !empty($post['nombre_proyecto'])
                ? $post['nombre_proyecto']
                : "Proyecto PP #{$idPostulacionGreen} - " . ($data['nombre'] ?? 'Presupuesto Participativo');

            $descripcionProyecto = !empty($post['descripcion_proyecto'])
                ? $post['descripcion_proyecto']
                : "Postulación #{$idPostulacionGreen} recibida desde módulo de Presupuesto Participativo (GREEN). Estado: {$estadoGreen}.";

            // Buscar si ya existía para idempotencia
            $proyecto = Proyectos::where('id_postulacion_green', $idPostulacionGreen)->first();
            $esNuevo = !$proyecto;

            if ($esNuevo) {
                $proyecto = new Proyectos();
                $proyecto->id_postulacion_green = $idPostulacionGreen;
                $proyecto->numero_tarjeton = $idx + 1;
            }

            $proyecto->id_convocatoria_green = $idConvocatoria;
            $proyecto->detalle = Str::limit($nombreProyecto, 490);
            $proyecto->descripcion = Str::limit($descripcionProyecto, 590);
            $proyecto->tipo = 'Proyecto';
            $proyecto->subtipo = $comunaDefaultId ?? 1; // ID comuna en parametros_detalle
            $proyecto->id_tipo = $tipoProyecto->id;
            $proyecto->estado = 1; // Activo en Vota Ya
            $proyecto->estado_green = $estadoGreen;
            $proyecto->save();

            // Asociar a los eventos hijos de las comunas en hash_proyectos
            foreach ($eventosHijosPorComuna as $comunaId => $eventoHijo) {
                Hash_proyectos::firstOrCreate([
                    'id_proyecto' => $proyecto->id,
                    'id_evento' => $eventoHijo->id,
                ]);
            }

            if ($esNuevo) {
                $proyectosCreados++;
            } else {
                $proyectosActualizados++;
            }
        }

        $resumen = [
            'ok' => true,
            'convocatoria_id' => $idConvocatoria,
            'convocatoria_nombre' => $data['nombre'] ?? '',
            'evento_padre_id' => $eventoPadre->id,
            'evento_padre_nombre' => $eventoPadre->nombre,
            'comunas_homologadas' => count($comunasHomologadas),
            'eventos_hijos_asociados' => count($eventosHijosPorComuna),
            'total_postulaciones' => count($postulaciones),
            'proyectos_creados' => $proyectosCreados,
            'proyectos_actualizados' => $proyectosActualizados,
        ];

        $this->registrarLog([
            'tipo' => 'INBOUND_SYNC',
            'id_convocatoria_green' => $idConvocatoria,
            'id_evento_vota' => $eventoPadre->id,
            'endpoint' => "{$this->baseUrl}/convocatorias/{$idConvocatoria}",
            'metodo' => 'GET',
            'respuesta_recibida' => json_encode($resumen),
            'http_status' => 200,
            'estado' => 'EXITOSO',
        ]);

        return $resumen;
    }

    /**
     * 4. Envío de resultados y proyectos ganadores a GREEN
     * POST o PUT acordado con GREEN
     */
    public function enviarResultadosGanadores(int $idConvocatoria, array $ganadores, ?int $idEvento = null): array
    {
        $endpoint = "{$this->baseUrl}/proyectos/actualizar-estado";

        $payload = [
            'id_convocatoria' => $idConvocatoria,
            'origen' => 'VOTA_YA',
            'fecha_consolidacion' => Carbon::now()->toIso8601String(),
            'ganadores' => array_map(function ($g) {
                return [
                    'id_postulacion' => $g['id_postulacion_green'] ?? $g['id_postulacion'],
                    'estado' => 'GANADOR',
                    'total_votos' => $g['total_votos'] ?? 0,
                    'posicion' => $g['posicion'] ?? 1,
                ];
            }, $ganadores),
        ];

        // Idempotency Key única para evitar duplicados en la red
        $idempotencyKey = "votaya_res_{$idConvocatoria}_" . md5(json_encode($payload));

        try {
            $response = Http::withToken($this->token)
                ->withHeaders(['X-Idempotency-Key' => $idempotencyKey])
                ->timeout($this->timeout)
                ->acceptJson()
                ->post($endpoint, $payload);

            $exitoso = $response->successful();

            $log = $this->registrarLog([
                'tipo' => 'OUTBOUND_RESULTADOS',
                'id_convocatoria_green' => $idConvocatoria,
                'id_evento_vota' => $idEvento,
                'endpoint' => $endpoint,
                'metodo' => 'POST',
                'payload_enviado' => $payload,
                'respuesta_recibida' => $response->body(),
                'http_status' => $response->status(),
                'estado' => $exitoso ? 'EXITOSO' : 'FALLIDO',
                'ultimo_error' => $exitoso ? null : ($response->json('message') ?? "HTTP {$response->status()}"),
            ]);

            return [
                'ok' => $exitoso,
                'log_id' => $log->id,
                'status' => $response->status(),
                'respuesta' => $response->json() ?? $response->body(),
                'error' => $exitoso ? null : ($response->json('message') ?? "Error {$response->status()}"),
            ];
        } catch (\Throwable $th) {
            $log = $this->registrarLog([
                'tipo' => 'OUTBOUND_RESULTADOS',
                'id_convocatoria_green' => $idConvocatoria,
                'id_evento_vota' => $idEvento,
                'endpoint' => $endpoint,
                'metodo' => 'POST',
                'payload_enviado' => $payload,
                'http_status' => 0,
                'estado' => 'FALLIDO',
                'ultimo_error' => $th->getMessage(),
            ]);

            return [
                'ok' => false,
                'log_id' => $log->id,
                'error' => $th->getMessage(),
            ];
        }
    }

    /**
     * Homologa las comunas que vienen de GREEN con las de parametros_detalle (codParametro = 'com01')
     */
    protected function homologarComunas(array $comunasGreen): array
    {
        $homologadas = [];
        $catalogoVota = ParametrosDetalle::where('codParametro', 'com01')->get();

        foreach ($comunasGreen as $cg) {
            $greenId = $cg['id'];
            $greenNombre = $cg['nombre'];
            $greenTipo = $cg['tipo_comuna'] ?? null;
            $greenGrupo = $cg['grupo'] ?? null;

            // 1. Buscar en tabla de homologación previa
            $registroHomologado = PpHomologacionComuna::where('green_comuna_id', $greenId)->first();

            if ($registroHomologado && $registroHomologado->vota_comuna_id) {
                $homologadas[$greenId] = [
                    'green_id' => $greenId,
                    'green_nombre' => $greenNombre,
                    'vota_id' => $registroHomologado->vota_comuna_id,
                    'vota_nombre' => $registroHomologado->vota_nombre,
                ];
                continue;
            }

            // 2. Búsqueda automática por coincidencia fonética/texto
            $nombreLimpio = $this->normalizarTexto($greenNombre);
            $votaMatch = $catalogoVota->first(function ($c) use ($nombreLimpio) {
                $detalleLimpio = $this->normalizarTexto($c->detalle);
                return str_contains($detalleLimpio, $nombreLimpio) || str_contains($nombreLimpio, $detalleLimpio);
            });

            if (!$votaMatch && is_numeric($greenId)) {
                // Fallback por ID directo si coincide
                $votaMatch = $catalogoVota->firstWhere('id', $greenId);
            }

            $votaId = $votaMatch ? $votaMatch->id : null;
            $votaNombre = $votaMatch ? $votaMatch->detalle : null;

            // Guardar o actualizar registro de homologación
            PpHomologacionComuna::updateOrCreate(
                ['green_comuna_id' => $greenId],
                [
                    'green_nombre' => $greenNombre,
                    'green_tipo' => $greenTipo,
                    'green_grupo' => $greenGrupo,
                    'vota_comuna_id' => $votaId,
                    'vota_nombre' => $votaNombre,
                    'homologado_manual' => false,
                ]
            );

            if ($votaId) {
                $homologadas[$greenId] = [
                    'green_id' => $greenId,
                    'green_nombre' => $greenNombre,
                    'vota_id' => $votaId,
                    'vota_nombre' => $votaNombre,
                ];
            }
        }

        return $homologadas;
    }

    /**
     * Resuelve o crea el evento padre de la convocatoria
     */
    protected function resolverEventoPadre(array $convocatoriaData, ?int $idEventoPadre = null): Eventos
    {
        if ($idEventoPadre) {
            $evento = Eventos::find($idEventoPadre);
            if ($evento) return $evento;
        }

        $nombreConvocatoria = $convocatoriaData['nombre'] ?? 'Presupuesto Participativo ' . date('Y');

        $evento = Eventos::where('nombre', $nombreConvocatoria)
            ->whereNull('evento_padre')
            ->first();

        if (!$evento) {
            $fechaInicio = $convocatoriaData['fecha_apertura'] ?? Carbon::now()->format('Y-m-d H:i:s');
            $fechaFin = $convocatoriaData['fecha_cierre'] ?? Carbon::now()->addDays(30)->format('Y-m-d H:i:s');

            $evento = Eventos::create([
                'nombre' => $nombreConvocatoria,
                'descripcion' => $convocatoriaData['descripcion'] ?? 'Evento principal de Presupuesto Participativo sincronizado desde GREEN.',
                'dependencias' => 'Secretaría de Planeación - Presupuesto Participativo',
                'tipos' => 'Proyecto',
                'evento_padre' => null,
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin,
                'estado' => 'Activo',
            ]);
        }

        return $evento;
    }

    /**
     * Resuelve o crea los eventos hijos asociados a cada comuna de la convocatoria
     */
    protected function resolverEventosHijos(Eventos $eventoPadre, array $comunasHomologadas): array
    {
        $hijos = [];

        foreach ($comunasHomologadas as $comuna) {
            $comunaId = $comuna['vota_id'];
            $comunaNombre = $comuna['vota_nombre'] ?? $comuna['green_nombre'];

            // Buscar si ya existe un evento hijo para esta comuna y evento padre
            $eventoHijo = Eventos::where('evento_padre', $eventoPadre->id)
                ->where('nombre', 'like', "%{$comunaNombre}%")
                ->first();

            if (!$eventoHijo) {
                $eventoHijo = Eventos::create([
                    'nombre' => "{$eventoPadre->nombre} - {$comunaNombre}",
                    'descripcion' => "Votación de proyectos para {$comunaNombre}.",
                    'dependencias' => $eventoPadre->dependencias,
                    'tipos' => 'Proyecto',
                    'evento_padre' => $eventoPadre->id,
                    'fecha_inicio' => $eventoPadre->fecha_inicio,
                    'fecha_fin' => $eventoPadre->fecha_fin,
                    'estado' => 'Activo',
                ]);
            }

            // Asegurar registro en hash_eventos_hijos
            Hash_eventos_hijos::firstOrCreate([
                'id_evento_padre' => $eventoPadre->id,
                'id_evento_hijo' => $eventoHijo->id,
            ]);

            $hijos[$comunaId] = $eventoHijo;
        }

        return $hijos;
    }

    /**
     * Normaliza cadenas para comparación sin tildes ni mayúsculas
     */
    protected function normalizarTexto(string $str): string
    {
        $str = mb_strtolower(trim($str), 'UTF-8');
        $str = str_replace(
            ['á', 'é', 'í', 'ó', 'ú', 'ñ', 'comuna', 'corregimiento', '-', '_'],
            ['a', 'e', 'i', 'o', 'u', 'n', '', '', ' ', ' '],
            $str
        );
        return trim(preg_replace('/\s+/', ' ', $str));
    }

    /**
     * Registra un log de auditoría en pp_integracion_logs
     */
    protected function registrarLog(array $datos): PpIntegracionLog
    {
        try {
            return PpIntegracionLog::create($datos);
        } catch (\Throwable $th) {
            Log::error('GreenPpService: no se pudo guardar log de auditoría', ['error' => $th->getMessage()]);
            return new PpIntegracionLog($datos);
        }
    }
}

