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
                'relacion' => $votante->relacion,
            'comuna' => $this->limpiarNombreComuna($votante->comuna),
            'barrio' => $votante->barrio,
            'correo' => $email,
            'telefono' => $celular,
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
     * Sincroniza un votante local con Central Data de forma tolerante a fallos.
     */
    public function syncVotante($votante, ?string $email = null, ?string $celular = null): array
    {
        $payload = $this->buildPayloadFromVotante($votante, $email, $celular);

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
