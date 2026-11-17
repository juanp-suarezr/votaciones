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
        $rawUrl = rtrim(config('services.green_pp.base_url', 'http://localhost/api/v1/pp'), '/');

        // Normalizar para asegurar que incluya el prefijo /api/v1/pp
        if (!str_ends_with($rawUrl, '/api/v1/pp') && !str_contains($rawUrl, '/api/v1/pp')) {
            $rawUrl .= '/api/v1/pp';
        }

        $this->baseUrl = $rawUrl;
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
     * 
     * Lógica institucional de VOTA YA:
     * - Evento Padre: Evento general institucional "Presupuesto Participativo" (evento_padre = 1).
     * - Evento Hijo: Evento de la VIGENCIA (ej. "Presupuesto Participativo 2026", en hash_eventos_hijos).
     * - Proyectos: Cada proyecto guarda su id_postulacion_green y su comuna en `subtipo` (parametros_detalle com01).
     * - Tabla puente hash_proyectos: Relaciona (id_evento = Evento Hijo Vigencia, id_proyecto = proyecto->id).
     */
    public function sincronizarConvocatoria(int $idConvocatoria, ?int $idEventoVigencia = null): array
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

        // 2. Resolver el Evento Padre institucional ("Presupuesto Participativo")
        $eventoPadre = $this->resolverEventoPadreInstitucional();

        // 3. Resolver o crear el Evento Hijo correspondiente a la VIGENCIA de la convocatoria
        $eventoVigencia = $this->resolverEventoHijoVigencia($eventoPadre, $data, $idEventoVigencia);

        // 4. Importar o actualizar las postulaciones como proyectos locales
        $proyectosCreados = 0;
        $proyectosActualizados = 0;

        // Tipo de proyecto predeterminado (Presupuesto Participativo)
        $tipoProyecto = Tipo_proyectos::firstOrCreate(
            ['nombre' => 'Presupuesto Participativo'],
            ['nombre' => 'Presupuesto Participativo']
        );

        // Determinar comuna principal o primera homologada para asignar a subtipo
        $comunaDefaultId = !empty($comunasHomologadas) ? reset($comunasHomologadas)['vota_id'] : 1;

        foreach ($postulaciones as $idx => $post) {
            $idPostulacionGreen = $post['id'] ?? null;
            if (!$idPostulacionGreen) continue;

            $estadoGreen = is_array($post['estado'] ?? null)
                ? ($post['estado']['codigo'] ?? 'EN_REVISION')
                : ($post['estado'] ?? 'EN_REVISION');

            // En la v1 de GREEN nombre_proyecto y descripcion_proyecto vienen en null
            $nombreProyecto = !empty($post['nombre_proyecto'])
                ? $post['nombre_proyecto']
                : "Proyecto PP #{$idPostulacionGreen} - " . ($data['nombre'] ?? 'Presupuesto Participativo');

            $descripcionProyecto = !empty($post['descripcion_proyecto'])
                ? $post['descripcion_proyecto']
                : "Postulación #{$idPostulacionGreen} recibida desde módulo de Presupuesto Participativo (GREEN). Estado: {$estadoGreen}.";

            // Buscar si ya existía para garantizar idempotencia
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
            $proyecto->subtipo = $comunaDefaultId; // ID de comuna/corregimiento en parametros_detalle com01
            $proyecto->id_tipo = $tipoProyecto->id;
            $proyecto->estado = 1; // Activo en Vota Ya
            $proyecto->estado_green = $estadoGreen;
            $proyecto->save();

            // Vincular en hash_proyectos con el Evento Hijo (la Vigencia de Presupuesto Participativo)
            Hash_proyectos::firstOrCreate([
                'id_proyecto' => $proyecto->id,
                'id_evento' => $eventoVigencia->id,
            ]);

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
            'evento_vigencia_id' => $eventoVigencia->id,
            'evento_vigencia_nombre' => $eventoVigencia->nombre,
            'comunas_homologadas' => count($comunasHomologadas),
            'total_postulaciones' => count($postulaciones),
            'proyectos_creados' => $proyectosCreados,
            'proyectos_actualizados' => $proyectosActualizados,
        ];

        $this->registrarLog([
            'tipo' => 'INBOUND_SYNC',
            'id_convocatoria_green' => $idConvocatoria,
            'id_evento_vota' => $eventoVigencia->id,
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
     * Resuelve o crea el Evento Padre institucional ("Presupuesto Participativo") con evento_padre = 1
     */
    protected function resolverEventoPadreInstitucional(): Eventos
    {
        $padre = Eventos::where('evento_padre', 1)
            ->where('nombre', 'like', '%Presupuesto%')
            ->first();

        if (!$padre) {
            $padre = Eventos::where('nombre', 'Presupuesto Participativo')
                ->first();
        }

        if (!$padre) {
            $padre = Eventos::create([
                'nombre' => 'Presupuesto Participativo',
                'descripcion' => 'Evento Padre Institucional de Presupuesto Participativo de la Alcaldía de Pereira.',
                'dependencias' => 'Secretaría de Planeación - Presupuesto Participativo',
                'tipos' => 'Proyecto',
                'evento_padre' => 1,
                'fecha_inicio' => Carbon::now()->startOfYear()->format('Y-m-d H:i:s'),
                'fecha_fin' => Carbon::now()->endOfYear()->format('Y-m-d H:i:s'),
                'estado' => 'Activo',
            ]);
        } else {
            // Asegurar que quede marcado como evento_padre = 1
            if ($padre->evento_padre != 1) {
                $padre->evento_padre = 1;
                $padre->save();
            }
        }

        return $padre;
    }

    /**
     * Resuelve o crea el Evento Hijo correspondiente a la VIGENCIA (ej: "Presupuesto Participativo 2026")
     * y lo vincula al Evento Padre en hash_eventos_hijos.
     */
    protected function resolverEventoHijoVigencia(Eventos $eventoPadre, array $convocatoriaData, ?int $idEventoVigencia = null): Eventos
    {
        if ($idEventoVigencia) {
            $evento = Eventos::find($idEventoVigencia);
            if ($evento) return $evento;
        }

        // Determinar año de la vigencia a partir del nombre de la convocatoria o fecha
        $nombreConvocatoria = $convocatoriaData['nombre'] ?? '';
        $anio = date('Y');
        if (preg_match('/20\d{2}/', $nombreConvocatoria, $matches)) {
            $anio = $matches[0];
        }

        $nombreVigencia = "Presupuesto Participativo {$anio}";

        $eventoHijo = Eventos::where('nombre', $nombreVigencia)
            ->where(function ($q) {
                $q->whereNull('evento_padre')->orWhere('evento_padre', 0);
            })
            ->first();

        if (!$eventoHijo) {
            $fechaInicio = $convocatoriaData['fecha_apertura'] ?? Carbon::now()->format('Y-m-d H:i:s');
            $fechaFin = $convocatoriaData['fecha_cierre'] ?? Carbon::now()->addDays(30)->format('Y-m-d H:i:s');

            $eventoHijo = Eventos::create([
                'nombre' => $nombreVigencia,
                'descripcion' => $convocatoriaData['descripcion'] ?? "Votaciones de proyectos de Presupuesto Participativo - Vigencia {$anio}.",
                'dependencias' => $eventoPadre->dependencias,
                'tipos' => 'Proyecto',
                'evento_padre' => 0,
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin,
                'estado' => 'Activo',
            ]);
        }

        // Vincular en hash_eventos_hijos (id_evento_padre, id_evento_hijo)
        Hash_eventos_hijos::firstOrCreate([
            'id_evento_padre' => $eventoPadre->id,
            'id_evento_hijo' => $eventoHijo->id,
        ]);

        return $eventoHijo;
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

