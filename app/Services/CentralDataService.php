<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CentralDataService
{
    protected string $baseUrl;
    protected string $slug;
    protected string $secret;
    protected string $sourceProject;

    protected const TOKEN_CACHE_KEY = 'central_data_token';
    protected const TOKEN_TTL_MINUTES = 55;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.central_data.url', env('CENTRAL_DATA_URL')), '/');
        $this->slug = config('services.central_data.slug', env('CENTRAL_DATA_SLUG'));
        $this->secret = config('services.central_data.secret', env('CENTRAL_DATA_SECRET'));
        $this->sourceProject = config('services.central_data.project', env('CENTRAL_DATA_PROJECT', 'votaciones'));
    }

    /**
     * Obtiene (y cachea) el token de autenticación del API Central Data.
     */
    public function getToken(bool $forceRefresh = false): ?string
    {
        if ($forceRefresh) {
            Cache::forget(self::TOKEN_CACHE_KEY);
        }

        return Cache::remember(self::TOKEN_CACHE_KEY, now()->addMinutes(self::TOKEN_TTL_MINUTES), function () {
            $response = Http::timeout(15)->post($this->baseUrl . '/auth/token', [
                'slug' => $this->slug,
                'secret' => $this->secret,
            ]);

            if (!$response->successful() || !isset($response['access_token'])) {
                Log::error('CentralData: fallo al obtener token', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return null;
            }

            return $response['access_token'];
        });
    }

    /**
     * Consulta una persona por tipo y número de documento.
     *
     * @return array{ok: bool, status: int, data: mixed, error?: string}
     */
    public function findPerson(string $tipoDocumento, string $numeroDocumento, ?string $sourceProject): array
    {
        $token = $this->getToken();
        if (!$token) {
            return $this->fail('No fue posible autenticar con Central Data.');
        }

        $response = Http::timeout(15)
            ->withToken($token)
            ->get($this->baseUrl . '/persons/find', [
                'tipo_documento' => $tipoDocumento,
                'numero_documento' => $numeroDocumento,
                'source_project' => $sourceProject ?? 'votaciones',
            ]);

        

        if ($response->status() === 401) {
            $token = $this->getToken(true);
            if (!$token) {
                return $this->fail('No fue posible autenticar con Central Data.');
            }
            $response = Http::timeout(15)
                ->withToken($token)
                ->get($this->baseUrl . '/persons/find', [
                    'tipo_documento' => $tipoDocumento,
                    'numero_documento' => $numeroDocumento,
                    'source_project' => $sourceProject ?? 'votaciones',
                ]);
        }

        if ($response->successful()) {


            return ['ok' => true, 'status' => $response->status(), 'data' => $response->json()];
        }

        Log::warning('CentralData: find falló', [
            'tipo_documento' => $tipoDocumento,
            'numero_documento' => $numeroDocumento,
            'status' => $response->status(),
            'body' => $response->body(),
        ]);

        return [
            'ok' => false,
            'status' => $response->status(),
            'data' => null,
            'error' => 'No se encontró la persona en Central Data.',
        ];
    }

    /**
     * Sincroniza (crea/actualiza) una persona en Central Data.
     *
     * @return array{ok: bool, status: int, data: mixed, error?: string}
     */
    public function syncPerson(array $payload): array
    {
        $payload['source_project'] = $payload['source_project'] ?? $this->sourceProject;

        $token = $this->getToken();
        if (!$token) {
            return $this->fail('No fue posible autenticar con Central Data.');
        }

        $response = Http::timeout(15)
            ->withToken($token)
            ->withHeaders(['Content-Type' => 'application/json'])
            ->post($this->baseUrl . '/persons/sync', $payload);

        if ($response->status() === 401) {
            $token = $this->getToken(true);
            if (!$token) {
                return $this->fail('No fue posible autenticar con Central Data.');
            }
            $response = Http::timeout(15)
                ->withToken($token)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post($this->baseUrl . '/persons/sync', $payload);
        }

        if ($response->successful()) {
            return ['ok' => true, 'status' => $response->status(), 'data' => $response->json()];
        }

        Log::warning('CentralData: sync falló', [
            'payload' => $payload,
            'status' => $response->status(),
            'body' => $response->body(),
        ]);

        return [
            'ok' => false,
            'status' => $response->status(),
            'data' => null,
            'error' => 'No se pudo sincronizar la persona en Central Data.',
        ];
    }

    /**
     * Construye el payload de sincronización a partir de un modelo Informacion_votantes.
     */
    public function buildPayloadFromVotante($votante, ?string $email = null, ?string $celular = null): array
    {
        $nombreCompleto = trim($votante->nombre ?? '');
        $partes = preg_split('/\s+/', $nombreCompleto, 2);
        $nombres = $partes[0] ?? $nombreCompleto;
        $apellidos = $partes[1] ?? '';

        $direccion = $votante->direccion ?? '';

        $edad = 0;
        if ($votante->nacimiento) {
            $edad = \Carbon\Carbon::parse($votante->nacimiento)->diffInYears(now());
        }

        return [
            'tipo_documento' => $this->getTipoDocumentoDataCenter($votante->tipo_documento) ?? $votante->tipo_documento,
            'numero_documento' => (string) $votante->identificacion,
            'nombres' => $nombres,
            'apellidos' => $apellidos,
            'fecha_nacimiento' => $votante->nacimiento ? date('Y-m-d', strtotime($votante->nacimiento)) : null,
            'edad' => $edad,
            'genero' => $this->getGeneroDataCenter($votante->genero) ?? $votante->genero,
            'etnia' => $this->getNombreEtniaDataCenter($votante->etnia ?? 'NA'),
            'nivel_estudio' => isset($votante->nivel_estudio) ? $this->getNombreNivelEstudioDataCenter($votante->nivel_estudio) : null,
            'condicion' => $this->getNombreCondicionDataCenter($votante->condicion ?? 'NA'),
            'relacion' => $votante->relacion ?: ($votante->planta ?? 'Funcionario'),
            'comuna' => $this->limpiarNombreComuna($votante->comuna),
            'barrio' => $votante->barrio,
            'correo' => $email ?? $votante->email,
            'telefono' => $celular ?? $votante->celular,
            'direccion' => [
                'via principal' => $direccion,
                'municipio' => '',
                'departamento' => '',
            ],
            'source_project' => $this->sourceProject,
        ];
    }

    //METODOS DATA CENTER
    public function limpiarNombreComuna(?string $nombre): ?string
    {
        if (!$nombre) {
            return null;
        }

        return preg_replace('/^(comuna|corregimiento|Comuna|Corregimiento)\s+/i', '', $nombre);
    }

    public function getTipoDocumentoDataCenter(string $tipo): ?string
    {
        $map = [
            'Tarjeta de Identidad' => 'TI',
            'Registro Civil' => 'RC',
            'Cédula Ciudadanía' => 'CC',
            'Cédula Extranjería' => 'CE',
            'Pasaporte' => 'PP',
            'Permiso por Protección Temporal' => 'PPT',
            'Permiso Especial PEP' => 'PEP',
            'NIT' => 'NIT',
            'Otro' => 'O',
        ];

        return $map[$tipo] ?? null;
    }

    public function getGeneroDataCenter($genero): ?string
    {
        return match ($genero) {
            'Masculino' => 'M',
            'Femenino'  => 'F',
            'Otro'      => 'O',
            default     => null,
        };
    }

    public function getDateDataCenter($edad) {
        $fecha = date('Y-m-d', strtotime('-' . $edad . ' years'));
        return $fecha;
    }

    public function getNombreEtniaDataCenter(string $code): ?string
    {
        $map = [
            'NA'         => 'No aplica',
            'mestizo'    => 'Mestizo',
            'afro'       => 'Afrodescendiente',
            'indigena'   => 'Indígena',
            'palanquero' => 'Palanquero',
            'rom'        => 'ROM',
        ];

        return $map[$code] ?? null;
    }

    public function getNombreNivelEstudioDataCenter(string $code): ?string
    {
        $map = [
            'NA'           => 'Ninguno',
            'primaria'     => 'Primaria',
            'secundaria'   => 'Secundaria',
            'tecnico'      => 'Tecnico',
            'tecnologico'  => 'Tecnologico',
            'universitario'=> 'Universitario',
            'postgrado'    => 'Postgrado',
        ];

        return $map[$code] ?? null;
    }

    public function getNombreCondicionDataCenter(string $code): ?string
    {
        $map = [
            'NA'              => 'Sin condición',
            'discapacitado'   => 'Persona con discapacidad',
            'desplazados'     => 'Desplazados',
            'victimasConfArm' => 'Victimas',
            'mujerCabHogar'   => 'Mujer cabeza de hogar',
            'hombreCabHogar'  => 'Padre cabeza de hogar',
            'habitanteCalle'  => 'Habitante de calle',
            'migrante'        => 'Migrante',
        ];

        return $map[$code] ?? null;
    }

    /**
     * Sincroniza un votante local con Central Data de forma no destructiva y tolerante a fallos.
     * Preserva la información previa existente en Central Data y en local (comuna, barrio, dirección, etc.)
     * evitando que campos preexistentes se sobrescriban con nulo o vacío.
     */
    public function syncVotante($votante, ?string $email = null, ?string $celular = null): array
    {
        $tipoDoc = $this->getTipoDocumentoDataCenter($votante->tipo_documento ?? 'CC') ?? ($votante->tipo_documento ?? 'CC');
        $numDoc = (string) $votante->identificacion;

        // 1. Consultar si la persona ya existe en Central Data para no pisar datos con null o vacío
        $existingPerson = null;
        try {
            $findRes = $this->findPerson($tipoDoc, $numDoc, $this->sourceProject);
            if (!empty($findRes['ok']) && !empty($findRes['data'])) {
                $existingPerson = isset($findRes['data']['data']) && is_array($findRes['data']['data'])
                    ? $findRes['data']['data']
                    : (is_array($findRes['data']) ? $findRes['data'] : null);
            }
        } catch (\Throwable $eFind) {
            Log::warning('CentralData: no fue posible consultar persona antes de sincronizar', [
                'doc' => $numDoc,
                'error' => $eFind->getMessage(),
            ]);
        }

        // 2. Construir payload inicial desde el modelo local
        $payload = $this->buildPayloadFromVotante($votante, $email, $celular);

        // 3. Fusión no destructiva: si Central Data ya tenía información previa y el payload local viene nulo/vacío/'NA', preservar Central Data
        if ($existingPerson && is_array($existingPerson)) {
            $camposPreservables = [
                'comuna',
                'barrio',
                'genero',
                'etnia',
                'condicion',
                'nivel_estudio',
                'fecha_nacimiento',
                'edad',
                'correo',
                'telefono',
                'dignatario',
                'relacion',
            ];

            foreach ($camposPreservables as $campo) {
                $valorLocal = $payload[$campo] ?? null;
                $valorCentral = $existingPerson[$campo] ?? null;

                $esVacioLocal = empty($valorLocal) || in_array($valorLocal, ['NA', 'No aplica', 'Sin condición', 'null']);
                $tieneValorCentral = !empty($valorCentral) && !in_array($valorCentral, ['NA', 'No aplica', 'Sin condición', 'null']);

                // Si en local está vacío o sin dato, pero Central Data ya lo tenía, PRESERVAR lo de Central Data
                if ($esVacioLocal && $tieneValorCentral) {
                    $payload[$campo] = $valorCentral;
                }
            }

            // Preservar estructura de Dirección
            $dirExistente = $existingPerson['direccion'] ?? null;
            if ($dirExistente) {
                if (is_array($dirExistente)) {
                    if (empty($payload['direccion']['via principal']) && !empty($dirExistente['via principal'])) {
                        $payload['direccion']['via principal'] = $dirExistente['via principal'];
                    }
                    if (empty($payload['direccion']['municipio']) && !empty($dirExistente['municipio'])) {
                        $payload['direccion']['municipio'] = $dirExistente['municipio'];
                    }
                    if (empty($payload['direccion']['departamento']) && !empty($dirExistente['departamento'])) {
                        $payload['direccion']['departamento'] = $dirExistente['departamento'];
                    }
                } elseif (is_string($dirExistente) && !empty($dirExistente) && empty($payload['direccion']['via principal'])) {
                    $payload['direccion']['via principal'] = $dirExistente;
                }
            }

            // 4. Beneficio de doble vía: Si en el votante local faltaban comuna/barrio/dirección, enriquecer el votante local sin tocar lo demás
            $modificadoLocal = false;
            if (empty($votante->comuna) && !empty($payload['comuna'])) {
                $votante->comuna = $payload['comuna'];
                $modificadoLocal = true;
            }
            if (empty($votante->barrio) && !empty($payload['barrio'])) {
                $votante->barrio = $payload['barrio'];
                $modificadoLocal = true;
            }
            if (empty($votante->direccion) && !empty($payload['direccion']['via principal'])) {
                $votante->direccion = $payload['direccion']['via principal'];
                $modificadoLocal = true;
            }
            if (empty($votante->nacimiento) && !empty($payload['fecha_nacimiento'])) {
                $votante->nacimiento = $payload['fecha_nacimiento'];
                $modificadoLocal = true;
            }
            if (empty($votante->genero) && !empty($payload['genero'])) {
                $votante->genero = $payload['genero'];
                $modificadoLocal = true;
            }
            if ($modificadoLocal) {
                try {
                    $votante->save();
                } catch (\Throwable $eSave) {
                    Log::warning('CentralData: no se pudo actualizar datos enriquecidos en votante local', ['error' => $eSave->getMessage()]);
                }
            }
        }

        try {
            $result = $this->syncPerson($payload);
        } catch (\Exception $e) {
            Log::error('CentralData: excepción en syncVotante', ['exception' => $e->getMessage()]);
            return ['ok' => false, 'status' => 0, 'data' => null, 'error' => $e->getMessage()];
        }

        return $result;
    }

    protected function fail(string $message): array
    {
        return ['ok' => false, 'status' => 401, 'data' => null, 'error' => $message];
    }
}
