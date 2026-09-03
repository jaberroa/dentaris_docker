<?php

namespace App\Console\Commands;

use App\Modules\Clinics\Services\ClinicalOwnershipBackfillService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class BackfillClinicalOwnership extends Command
{
    protected $signature = 'clinics:backfill-ownership
                            {--clinic-code=DEN-CL-001 : Código estable de la clínica histórica}
                            {--execute : Confirma la escritura transaccional de clinic_id}';

    protected $description = 'Concilia la pertenencia clínica histórica de Pacientes y Personal';

    public function handle(ClinicalOwnershipBackfillService $service): int
    {
        $runId = (string) Str::uuid();

        try {
            $summary = $this->option('execute')
                ? $service->execute((string) $this->option('clinic-code'))
                : [
                    'status' => 'dry_run',
                    'run_id' => $runId,
                    'clinic_code' => (string) $this->option('clinic-code'),
                    'inspection' => $service->preview((string) $this->option('clinic-code')),
                ];

            Log::info('Clinical ownership backfill summary', $summary);
            $this->line((string) json_encode($summary, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

            return self::SUCCESS;
        } catch (Throwable $exception) {
            $this->error('Backfill bloqueado: '.$exception->getMessage());

            return self::FAILURE;
        }
    }
}
