<?php

namespace App\Jobs;

use App\Models\PpIntegracionLog;
use App\Services\GreenPpService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class EnviarResultadosPpJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;
    public array $backoff = [60, 300, 900, 3600]; // 1m, 5m, 15m, 1h

    protected int $idConvocatoria;
    protected array $ganadores;
    protected ?int $idEvento;
    protected ?int $logId;

    /**
     * Create a new job instance.
     */
    public function __construct(int $idConvocatoria, array $ganadores, ?int $idEvento = null, ?int $logId = null)
    {
        $this->idConvocatoria = $idConvocatoria;
        $this->ganadores = $ganadores;
        $this->idEvento = $idEvento;
        $this->logId = $logId;
    }

    /**
     * Execute the job.
     */
    public function handle(GreenPpService $service): void
    {
        $intentoActual = $this->attempts();

        Log::info("EnviarResultadosPpJob: enviando resultados para convocatoria {$this->idConvocatoria} (Intento {$intentoActual}/{$this->tries})");

        $resultado = $service->enviarResultadosGanadores(
            $this->idConvocatoria,
            $this->ganadores,
            $this->idEvento
        );

        $logId = $resultado['log_id'] ?? $this->logId;

        if ($logId) {
            $log = PpIntegracionLog::find($logId);
            if ($log) {
                $log->intentos = $intentoActual;
                $log->save();
            }
        }

        if (!$resultado['ok']) {
            if ($logId) {
                $log = PpIntegracionLog::find($logId);
                if ($log) {
                    $delay = $this->backoff[$intentoActual - 1] ?? 3600;
                    $log->estado = $intentoActual < $this->tries ? 'REINTENTANDO' : 'FALLIDO';
                    $log->proximo_reintento_at = $intentoActual < $this->tries ? Carbon::now()->addSeconds($delay) : null;
                    $log->save();
                }
            }

            throw new \Exception("Error enviando ganadores a GREEN: " . ($resultado['error'] ?? 'Error desconocido'));
        }

        if ($logId) {
            $log = PpIntegracionLog::find($logId);
            if ($log) {
                $log->estado = 'EXITOSO';
                $log->ultimo_error = null;
                $log->proximo_reintento_at = null;
                $log->save();
            }
        }

        Log::info("EnviarResultadosPpJob: resultados entregados exitosamente a GREEN para convocatoria {$this->idConvocatoria}");
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("EnviarResultadosPpJob: se agotaron los reintentos para la convocatoria {$this->idConvocatoria}", [
            'error' => $exception->getMessage(),
        ]);

        if ($this->logId) {
            $log = PpIntegracionLog::find($this->logId);
            if ($log) {
                $log->estado = 'FALLIDO';
                $log->ultimo_error = "Se agotaron los {$this->tries} reintentos: " . $exception->getMessage();
                $log->save();
            }
        }
    }
}

