<?php

namespace App\Modules\Clinics\Services;

use App\Models\User;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Reconciles nullable ownership only when an existing relationship proves it.
 *
 * The target clinic is never inferred from "the only clinic". Every updated
 * row must point to a parent that already belongs to the requested clinic.
 */
final class ClinicOwnedDomainTransitionService
{
    /** @var array<string, list<string>> */
    private const OWNED_TABLES = [
        'inventory_locations' => ['id', 'clinic_id', 'is_active'],
        'inventory' => ['id', 'clinic_id', 'product_id', 'inventory_location_id'],
        'inventory_movements' => ['id', 'clinic_id', 'inventory_id', 'product_id'],
        'invoices' => ['id', 'clinic_id', 'patient_id', 'staff_id'],
        'payments' => ['id', 'clinic_id', 'invoice_id', 'patient_id'],
    ];

    public function __construct(
        private readonly ConnectionInterface $connection,
        private readonly ClinicPermissionService $permissions,
    ) {}

    /** @return array<string, mixed> */
    public function preview(string $clinicCode, string $actorEmail): array
    {
        return $this->inspect($clinicCode, $actorEmail);
    }

    /** @return array<string, mixed> */
    public function execute(string $clinicCode, string $actorEmail): array
    {
        $runId = (string) Str::uuid();

        return $this->connection->transaction(function () use ($clinicCode, $actorEmail, $runId): array {
            $before = $this->inspect($clinicCode, $actorEmail, true);
            $this->assertExecutable($before);

            $clinicId = (int) $before['clinic']['id'];
            $updated = [
                'inventory_locations' => 0,
                'inventory' => $this->assignInventoryFromLocations($clinicId),
                'inventory_movements' => $this->assignMovementsFromInventory($clinicId),
                'invoices' => $this->assignInvoicesFromClinicalOwners($clinicId),
                'payments' => $this->assignPaymentsFromInvoices($clinicId),
            ];

            $after = $this->inspect($clinicCode, $actorEmail);
            $this->assertPostconditions($after);
            if ($before['hashes']['integrity'] !== $after['hashes']['integrity']) {
                throw new RuntimeException('Clinic-owned transition changed data outside clinic_id.');
            }

            $summary = [
                'status' => 'executed',
                'run_id' => $runId,
                'clinic_code' => $clinicCode,
                'clinic_id' => $clinicId,
                'actor_id' => (int) $before['actor']['id'],
                'updated' => $updated,
                'updated_total' => array_sum($updated),
                'before' => $this->summary($before),
                'after' => $this->summary($after),
            ];

            activity('security')
                ->causedBy(User::query()->findOrFail($summary['actor_id']))
                ->withProperties([
                    'run_id' => $summary['run_id'],
                    'clinic_id' => $summary['clinic_id'],
                    'updated' => $summary['updated'],
                    'before_hashes' => $summary['before']['hashes'],
                    'after_hashes' => $summary['after']['hashes'],
                ])
                ->log('clinic_owned_domains.transitioned');

            return $summary;
        }, 3);
    }

    /** @return array<string, mixed> */
    private function inspect(string $clinicCode, string $actorEmail, bool $lock = false): array
    {
        $errors = [];
        $schema = [];

        foreach (array_keys(self::OWNED_TABLES) as $table) {
            $schema[$table] = $this->schemaHasOwnership($table);
        }

        if (in_array(false, $schema, true)) {
            $errors[] = 'missing_clinic_ownership_schema';
        }

        $clinics = $this->connection->table('clinics')->where('code', $clinicCode)->get(['id', 'code', 'is_active']);
        $clinic = $clinics->first();

        if ($clinics->count() !== 1) {
            $errors[] = 'target_clinic_must_exist_exactly_once';
        } elseif (! (bool) $clinic->is_active) {
            $errors[] = 'target_clinic_must_be_active';
        }

        $actor = $this->connection->table('users')->where('email', $actorEmail)->first(['id', 'is_active']);
        if ($actor === null || ! (bool) $actor->is_active) {
            $errors[] = 'actor_must_exist_and_be_active';
        }

        $membership = null;
        if ($clinic !== null && $actor !== null) {
            $membership = $this->connection->table('clinic_memberships')
                ->where('clinic_id', $clinic->id)
                ->where('user_id', $actor->id)
                ->where('status', 'active')
                ->whereNotNull('activated_at')
                ->whereNull('suspended_at')
                ->first(['id', 'clinic_id', 'user_id']);
        }

        if ($membership === null) {
            $errors[] = 'actor_requires_active_membership';
        }

        $context = null;
        if ($clinic !== null && $actor !== null && $membership !== null) {
            $context = new \App\Modules\Clinics\Data\ClinicContext(
                (int) $actor->id,
                (int) $clinic->id,
                (int) $membership->id,
            );
            foreach (['manage_inventory', 'manage_billing'] as $permission) {
                $user = \App\Models\User::query()->find((int) $actor->id);
                if ($user === null || ! $this->permissions->allows($user, $permission, $context)) {
                    $errors[] = 'actor_missing_'.$permission;
                }
            }
        }

        if (in_array(false, $schema, true) || $clinic === null) {
            return [
                'clinic' => $clinic === null ? null : (array) $clinic,
                'actor' => $actor === null ? null : (array) $actor,
                'membership_id' => $membership?->id,
                'schema' => $schema,
                'tables' => $this->tableCountsWithoutOwnership(),
                'candidates' => [],
                'relations' => [],
                'hashes' => [
                    'integrity' => $this->hashes(false),
                    'ownership' => $this->hashes(true),
                ],
                'errors' => array_values(array_unique($errors)),
            ];
        }

        if ($lock) {
            foreach (array_keys(self::OWNED_TABLES) as $table) {
                $this->connection->table($table)->whereNull('clinic_id')->lockForUpdate()->get(['id']);
            }
        }

        $clinicId = (int) $clinic->id;
        $tables = $this->tableCountsWithOwnership($clinicId);
        $candidates = $this->candidateCounts($clinicId);
        $relations = $this->relationConflicts();

        if ($tables['inventory_locations']['pending'] > 0) {
            $errors[] = 'inventory_locations_have_no_unambiguous_parent';
        }

        foreach (['inventory', 'inventory_movements', 'invoices', 'payments'] as $table) {
            if ($tables[$table]['pending'] !== $candidates[$table]) {
                $errors[] = $table.'_has_ambiguous_pending_rows';
            }
        }

        if (array_sum($relations) > 0) {
            $errors[] = 'cross_clinic_or_broken_relations_detected';
        }

        return [
            'clinic' => (array) $clinic,
            'actor' => (array) $actor,
            'membership_id' => $membership?->id,
            'schema' => $schema,
            'tables' => $tables,
            'candidates' => $candidates,
            'relations' => $relations,
            'hashes' => [
                'integrity' => $this->hashes(false),
                'ownership' => $this->hashes(true),
            ],
            'errors' => array_values(array_unique($errors)),
        ];
    }

    private function assignInventoryFromLocations(int $clinicId): int
    {
        return $this->connection->table('inventory as stock')
            ->join('inventory_locations as location', 'location.id', '=', 'stock.inventory_location_id')
            ->whereNull('stock.clinic_id')
            ->where('location.clinic_id', $clinicId)
            ->update(['stock.clinic_id' => $clinicId]);
    }

    private function assignMovementsFromInventory(int $clinicId): int
    {
        return $this->connection->table('inventory_movements as movement')
            ->join('inventory as stock', 'stock.id', '=', 'movement.inventory_id')
            ->whereNull('movement.clinic_id')
            ->where('stock.clinic_id', $clinicId)
            ->whereColumn('stock.product_id', 'movement.product_id')
            ->update(['movement.clinic_id' => $clinicId]);
    }

    private function assignInvoicesFromClinicalOwners(int $clinicId): int
    {
        return $this->connection->table('invoices as invoice')
            ->join('patients as patient', 'patient.id', '=', 'invoice.patient_id')
            ->join('staff as professional', 'professional.id', '=', 'invoice.staff_id')
            ->whereNull('invoice.clinic_id')
            ->where('patient.clinic_id', $clinicId)
            ->where('professional.clinic_id', $clinicId)
            ->update(['invoice.clinic_id' => $clinicId]);
    }

    private function assignPaymentsFromInvoices(int $clinicId): int
    {
        return $this->connection->table('payments as payment')
            ->join('invoices as invoice', 'invoice.id', '=', 'payment.invoice_id')
            ->join('patients as patient', 'patient.id', '=', 'payment.patient_id')
            ->whereNull('payment.clinic_id')
            ->where('invoice.clinic_id', $clinicId)
            ->where('patient.clinic_id', $clinicId)
            ->whereColumn('invoice.patient_id', 'payment.patient_id')
            ->update(['payment.clinic_id' => $clinicId]);
    }

    /** @return array<string, int> */
    private function candidateCounts(int $clinicId): array
    {
        return [
            'inventory_locations' => 0,
            'inventory' => $this->connection->table('inventory as stock')
                ->join('inventory_locations as location', 'location.id', '=', 'stock.inventory_location_id')
                ->whereNull('stock.clinic_id')->where('location.clinic_id', $clinicId)->count(),
            'inventory_movements' => $this->connection->table('inventory_movements as movement')
                ->join('inventory as stock', 'stock.id', '=', 'movement.inventory_id')
                ->leftJoin('inventory_locations as location', 'location.id', '=', 'stock.inventory_location_id')
                ->whereNull('movement.clinic_id')
                ->where(function ($query) use ($clinicId): void {
                    $query->where('stock.clinic_id', $clinicId)
                        ->orWhere(function ($candidate) use ($clinicId): void {
                            $candidate->whereNull('stock.clinic_id')->where('location.clinic_id', $clinicId);
                        });
                })
                ->whereColumn('stock.product_id', 'movement.product_id')->count(),
            'invoices' => $this->connection->table('invoices as invoice')
                ->join('patients as patient', 'patient.id', '=', 'invoice.patient_id')
                ->join('staff as professional', 'professional.id', '=', 'invoice.staff_id')
                ->whereNull('invoice.clinic_id')->where('patient.clinic_id', $clinicId)
                ->where('professional.clinic_id', $clinicId)->count(),
            'payments' => $this->connection->table('payments as payment')
                ->join('invoices as invoice', 'invoice.id', '=', 'payment.invoice_id')
                ->join('patients as patient', 'patient.id', '=', 'payment.patient_id')
                ->join('staff as professional', 'professional.id', '=', 'invoice.staff_id')
                ->whereNull('payment.clinic_id')
                ->where(function ($query) use ($clinicId): void {
                    $query->where('invoice.clinic_id', $clinicId)
                        ->orWhere(function ($candidate) use ($clinicId): void {
                            $candidate->whereNull('invoice.clinic_id')
                                ->where('patient.clinic_id', $clinicId)
                                ->where('professional.clinic_id', $clinicId);
                        });
                })
                ->where('patient.clinic_id', $clinicId)
                ->whereColumn('invoice.patient_id', 'payment.patient_id')->count(),
        ];
    }

    /** @return array<string, int> */
    private function relationConflicts(): array
    {
        return [
            'inventory_location_mismatch' => $this->connection->table('inventory as stock')
                ->leftJoin('inventory_locations as location', 'location.id', '=', 'stock.inventory_location_id')
                ->whereNotNull('stock.inventory_location_id')->whereNotNull('stock.clinic_id')
                ->where(fn ($query) => $query->whereNull('location.id')->orWhereNull('location.clinic_id')
                    ->orWhereColumn('location.clinic_id', '<>', 'stock.clinic_id'))->count(),
            'movement_mismatch' => $this->connection->table('inventory_movements as movement')
                ->leftJoin('inventory as stock', 'stock.id', '=', 'movement.inventory_id')
                ->whereNotNull('movement.clinic_id')
                ->where(fn ($query) => $query->whereNull('stock.id')->orWhereNull('stock.clinic_id')
                    ->orWhereColumn('stock.clinic_id', '<>', 'movement.clinic_id')
                    ->orWhereColumn('stock.product_id', '<>', 'movement.product_id'))->count(),
            'invoice_mismatch' => $this->connection->table('invoices as invoice')
                ->leftJoin('patients as patient', 'patient.id', '=', 'invoice.patient_id')
                ->leftJoin('staff as professional', 'professional.id', '=', 'invoice.staff_id')
                ->whereNotNull('invoice.clinic_id')
                ->where(fn ($query) => $query->whereNull('patient.id')->orWhereNull('patient.clinic_id')
                    ->orWhereColumn('patient.clinic_id', '<>', 'invoice.clinic_id')
                    ->orWhereNull('professional.id')->orWhereNull('professional.clinic_id')
                    ->orWhereColumn('professional.clinic_id', '<>', 'invoice.clinic_id'))->count(),
            'payment_mismatch' => $this->connection->table('payments as payment')
                ->leftJoin('invoices as invoice', 'invoice.id', '=', 'payment.invoice_id')
                ->leftJoin('patients as patient', 'patient.id', '=', 'payment.patient_id')
                ->whereNotNull('payment.clinic_id')
                ->where(fn ($query) => $query->whereNull('invoice.id')->orWhereNull('invoice.clinic_id')
                    ->orWhereColumn('invoice.clinic_id', '<>', 'payment.clinic_id')
                    ->orWhereNull('patient.id')->orWhereNull('patient.clinic_id')
                    ->orWhereColumn('patient.clinic_id', '<>', 'payment.clinic_id')
                    ->orWhereColumn('invoice.patient_id', '<>', 'payment.patient_id'))->count(),
        ];
    }

    /** @return array<string, array<string, int>> */
    private function tableCountsWithOwnership(int $clinicId): array
    {
        $result = [];
        foreach (array_keys(self::OWNED_TABLES) as $table) {
            $result[$table] = [
                'total' => $this->connection->table($table)->count(),
                'pending' => $this->connection->table($table)->whereNull('clinic_id')->count(),
                'target' => $this->connection->table($table)->where('clinic_id', $clinicId)->count(),
                'other' => $this->connection->table($table)->whereNotNull('clinic_id')->where('clinic_id', '<>', $clinicId)->count(),
            ];
        }

        return $result;
    }

    /** @return array<string, array<string, int|null>> */
    private function tableCountsWithoutOwnership(): array
    {
        $result = [];
        foreach (array_keys(self::OWNED_TABLES) as $table) {
            $result[$table] = [
                'total' => $this->connection->getSchemaBuilder()->hasTable($table)
                    ? $this->connection->table($table)->count()
                    : null,
                'pending' => null,
                'target' => null,
                'other' => null,
            ];
        }

        return $result;
    }

    /** @return array<string, string|null> */
    private function hashes(bool $withOwnership): array
    {
        $hashes = [];
        $schema = $this->connection->getSchemaBuilder();
        foreach (array_keys(self::OWNED_TABLES) as $table) {
            if (! $schema->hasTable($table)) {
                $hashes[$table] = null;
                continue;
            }

            $selected = array_values(array_filter(
                $schema->getColumnListing($table),
                static fn (string $column): bool => $withOwnership || $column !== 'clinic_id',
            ));
            $hash = hash_init('sha256');
            $this->connection->table($table)->select($selected)->orderBy('id')->chunk(500, function ($rows) use ($hash): void {
                foreach ($rows as $row) {
                    hash_update($hash, json_encode((array) $row, JSON_THROW_ON_ERROR)."\n");
                }
            });
            $hashes[$table] = hash_final($hash);
        }

        return $hashes;
    }

    private function schemaHasOwnership(string $table): bool
    {
        $schema = $this->connection->getSchemaBuilder();

        return $schema->hasTable($table) && $schema->hasColumn($table, 'clinic_id');
    }

    /** @param array<string, mixed> $state */
    private function assertExecutable(array $state): void
    {
        if ($state['errors'] !== []) {
            throw new RuntimeException('Clinic-owned transition blocked: '.implode(',', $state['errors']));
        }
    }

    /** @param array<string, mixed> $state */
    private function assertPostconditions(array $state): void
    {
        $this->assertExecutable($state);

        foreach ($state['tables'] as $table => $counts) {
            if ($counts['pending'] !== 0) {
                throw new RuntimeException("Clinic-owned transition left pending rows in {$table}.");
            }
        }
    }

    /** @param array<string, mixed> $state @return array<string, mixed> */
    private function summary(array $state): array
    {
        return [
            'tables' => $state['tables'],
            'relations' => $state['relations'],
            'hashes' => $state['hashes'],
            'errors' => $state['errors'],
        ];
    }
}
