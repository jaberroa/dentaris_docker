<?php

namespace App\Console\Commands;

use App\Modules\Clinics\Services\ClinicOwnedDomainTransitionService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class TransitionClinicOwnedDomains extends Command
{
    protected $signature = 'clinics:transition-owned-domains
                            {--clinic-code=DEN-CL-001 : Código estable de la clínica objetivo}
                            {--actor-email= : Identidad activa que autoriza la operación}
                            {--execute : Confirma la escritura transaccional de clinic_id}';

    protected $description = 'Audita y concilia propiedad clínica de inventario, facturación y pagos';

    public function handle(ClinicOwnedDomainTransitionService $service): int
    {
        $clinicCode = trim((string) $this->option('clinic-code'));
        $actorEmail = trim((string) $this->option('actor-email'));

        if ($actorEmail === '') {
            $this->error('La opción --actor-email es obligatoria.');

            return self::INVALID;
        }

        try {
            $summary = $this->option('execute')
                ? $service->execute($clinicCode, $actorEmail)
                : [
                    'status' => 'dry_run',
                    'run_id' => (string) Str::uuid(),
                    'clinic_code' => $clinicCode,
                    'inspection' => $service->preview($clinicCode, $actorEmail),
                ];

            Log::info('Clinic-owned domains transition summary', $summary);
            $this->line((string) json_encode($summary, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

            return self::SUCCESS;
        } catch (Throwable $exception) {
            Log::warning('Clinic-owned domains transition blocked', [
                'clinic_code' => $clinicCode,
                'error' => $exception->getMessage(),
            ]);
            $this->error('Transición bloqueada: '.$exception->getMessage());

            return self::FAILURE;
        }
    }
}
