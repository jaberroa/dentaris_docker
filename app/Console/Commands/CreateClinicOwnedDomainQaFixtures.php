<?php

namespace App\Console\Commands;

use App\Modules\Clinics\Services\ClinicOwnedDomainQaFixtureService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Throwable;

class CreateClinicOwnedDomainQaFixtures extends Command
{
    protected $signature = 'clinics:create-owned-domain-qa
                            {--clinic-code=DEN-CL-001 : Código estable de la clínica objetivo}
                            {--actor-email= : Identidad activa que autoriza y crea los datos QA}
                            {--count=5 : Registros visibles por cada vista; mínimo 5 y máximo 25}
                            {--execute : Confirma la creación transaccional e idempotente}';

    protected $description = 'Prepara datos QA identificados para validar inventario, facturación y pagos';

    public function handle(ClinicOwnedDomainQaFixtureService $service): int
    {
        $clinicCode = trim((string) $this->option('clinic-code'));
        $actorEmail = trim((string) $this->option('actor-email'));
        $count = filter_var($this->option('count'), FILTER_VALIDATE_INT);

        if ($actorEmail === '' || $count === false) {
            $this->error('Las opciones --actor-email y --count válido son obligatorias.');

            return self::INVALID;
        }

        try {
            $summary = $this->option('execute')
                ? $service->execute($clinicCode, $actorEmail, $count)
                : $service->preview($clinicCode, $actorEmail, $count);

            Log::info('Clinic-owned domains QA fixture summary', $summary);
            $this->line((string) json_encode($summary, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

            return self::SUCCESS;
        } catch (Throwable $exception) {
            Log::warning('Clinic-owned domains QA fixture blocked', [
                'clinic_code' => $clinicCode,
                'error' => $exception->getMessage(),
            ]);
            $this->error('Datos QA bloqueados: '.$exception->getMessage());

            return self::FAILURE;
        }
    }
}
