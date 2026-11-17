<?php

namespace App\Console\Commands;

use App\Services\GreenPpService;
use Illuminate\Console\Command;

class PpSincronizarCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pp:sincronizar 
                            {--id= : ID de la convocatoria específica a sincronizar}
                            {--evento-padre= : ID del evento padre local al cual vincular}
                            {--probar-envio= : Prueba el envío de ganadores para un ID de convocatoria}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Consulta y sincroniza convocatorias y proyectos desde la API de Presupuesto Participativo (GREEN)';

    /**
     * Execute the console command.
     */
    public function handle(GreenPpService $service)
    {
        $idConvocatoria = $this->option('id');
        $idEventoPadre = $this->option('evento-padre');
        $probarEnvio = $this->option('probar-envio');

        $this->info("=================================================");
        $this->info(" INTEGRACIÓN VOTA YA <-> PRESUPUESTO PARTICIPATIVO");
        $this->info(" Token: 1|hrpLFod7R2ppecAdGgN9d1LntnAn9PMDjRycMR2353377cdd");
        $this->info("=================================================");

        // Caso 1: Probar envío de resultados
        if ($probarEnvio) {
            $this->warn("Probando despacho de proyectos ganadores para convocatoria #{$probarEnvio}...");
            $ganadoresMock = [
                [
                    'id_postulacion_green' => 381,
                    'total_votos' => 450,
                    'posicion' => 1,
                ]
            ];

            $resEnvio = $service->enviarResultadosGanadores((int)$probarEnvio, $ganadoresMock);

            if ($resEnvio['ok']) {
                $this->info("✓ Envío exitoso a GREEN (Status: {$resEnvio['status']}). Log ID: {$resEnvio['log_id']}");
            } else {
                $this->error("✗ Fallo en el envío a GREEN: " . ($resEnvio['error'] ?? 'Error desconocido'));
                $this->line("Detalle guardado en pp_integracion_logs con ID: {$resEnvio['log_id']}");
            }
            return 0;
        }

        // Caso 2: Sincronizar convocatoria específica
        if ($idConvocatoria) {
            $this->info("Sincronizando convocatoria #{$idConvocatoria}...");
            $res = $service->sincronizarConvocatoria((int)$idConvocatoria, $idEventoPadre ? (int)$idEventoPadre : null);

            if ($res['ok']) {
                $this->info("✓ Sincronización finalizada exitosamente.");
                $this->table(['Parámetro', 'Valor'], [
                    ['Convocatoria ID', $res['convocatoria_id']],
                    ['Nombre', $res['convocatoria_nombre']],
                    ['Evento Padre ID', $res['evento_padre_id']],
                    ['Comunas Homologadas', $res['comunas_homologadas']],
                    ['Eventos Hijos Creados/Asociados', $res['eventos_hijos_asociados']],
                    ['Total Postulaciones', $res['total_postulaciones']],
                    ['Proyectos Creados', $res['proyectos_creados']],
                    ['Proyectos Actualizados', $res['proyectos_actualizados']],
                ]);
            } else {
                $this->error("✗ Error sincronizando convocatoria: " . ($res['error'] ?? 'Error desconocido'));
            }
            return 0;
        }

        // Caso 3: Listar convocatorias disponibles en GREEN
        $this->info("Consultando convocatorias disponibles en GREEN...");
        $res = $service->listarConvocatorias();

        if (!$res['ok']) {
            $this->error("Error conectando con la API de GREEN: " . ($res['error'] ?? 'Error de conexión'));
            $this->warn("Verifique que GREEN_PP_BASE_URL esté accesible y el token sea válido.");
            return 1;
        }

        $convocatorias = $res['data'] ?? [];

        if (empty($convocatorias)) {
            $this->warn("No se encontraron convocatorias en la API de GREEN.");
            return 0;
        }

        $rows = [];
        foreach ($convocatorias as $c) {
            $estado = is_array($c['estado'] ?? null) ? ($c['estado']['nombre'] ?? 'N/A') : ($c['estado'] ?? 'N/A');
            $tipo = is_array($c['tipo'] ?? null) ? ($c['tipo']['nombre'] ?? 'N/A') : ($c['tipo'] ?? 'N/A');
            $comunasCount = is_array($c['comunas'] ?? null) ? count($c['comunas']) : 0;

            $rows[] = [
                $c['id'] ?? 'N/A',
                $c['nombre'] ?? 'Sin nombre',
                $tipo,
                $estado,
                $c['fecha_apertura'] ?? 'N/A',
                $c['fecha_cierre'] ?? 'N/A',
                $comunasCount,
                $c['total_postulaciones'] ?? 0,
            ];
        }

        $this->table(
            ['ID', 'Nombre', 'Tipo', 'Estado', 'Apertura', 'Cierre', 'Comunas', 'Postulaciones'],
            $rows
        );

        $this->info("Para sincronizar una convocatoria ejecute:");
        $this->line("  php artisan pp:sincronizar --id=<ID_CONVOCATORIA>");

        return 0;
    }
}

