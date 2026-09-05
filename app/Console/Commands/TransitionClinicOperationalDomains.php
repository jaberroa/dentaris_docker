<?php

namespace App\Console\Commands;

use App\Modules\Clinics\Services\ClinicOperationalDomainTransitionService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class TransitionClinicOperationalDomains extends Command
{
    protected $signature = 'clinics:transition-operational-domains
                            {--clinic-code=DEN-CL-001 : Código estable de la clínica objetivo}
                            {--actor-id= : ID de la identidad activa que autoriza la operación}
                            {--execute : Confirma la escritura transaccional exclusiva de clinic_id}';

    protected $description = 'Audita y concilia propiedad clínica de proveedores, productos, compras y cotizaciones';

    public function handle(ClinicOperationalDomainTransitionService $service): int
    {
        $clinicCode = trim((string) $this->option('clinic-code'));
        $actorId = filter_var($this->option('actor-id'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($actorId === false) {
            $this->error('La opción --actor-id debe ser un entero positivo.');

            return self::INVALID;
        }

        try {
            $summary = $this->option('execute')
                ? $service->execute($clinicCode, $actorId)
                : [
                    'status' => 'dry_run',
                    'run_id' => (string) Str::uuid(),
                    'clinic_code' => $clinicCode,
                    'actor_id' => $actorId,
                    'inspection' => $service->preview($clinicCode, $actorId),
                ];

            Log::info('Clinic operational domains transition summary', $summary);
            $this->line((string) json_encode($summary, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

            return self::SUCCESS;
        } catch (Throwable $exception) {
            Log::warning('Clinic operational domains transition blocked', [
                'clinic_code' => $clinicCode,
                'actor_id' => $actorId,
                'error' => $exception->getMessage(),
            ]);
            $this->error('Transición bloqueada: '.$exception->getMessage());

            return self::FAILURE;
        }
    }
}
