<?php

namespace App\Modules\Clinics\Services;

use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Str;
use RuntimeException;

/** Creates clearly labelled, idempotent QA records for visual validation. */
final class ClinicOwnedDomainQaFixtureService
{
    public function __construct(
        private readonly ConnectionInterface $connection,
        private readonly ClinicOwnedDomainTransitionService $transition,
        private readonly ClinicOwnedDomainReadinessService $readiness,
        private readonly ClinicPermissionService $permissions,
    ) {}

    /** @return array<string, mixed> */
    public function preview(string $clinicCode, string $actorEmail, int $count): array
    {
        $this->assertCount($count);
        $transition = $this->transition->preview($clinicCode, $actorEmail);

        $visualPermissions = $this->visualPermissions($transition);

        return [
            'status' => 'dry_run',
            'clinic_code' => $clinicCode,
            'count_per_view' => $count,
            'transition' => $transition,
            'domains_ready' => [
                'inventory' => $this->readiness->isReady('inventory'),
                'billing' => $this->readiness->isReady('billing'),
                'procurement' => $this->readiness->isReady('procurement'),
                'quotes' => $this->readiness->isReady('quotes'),
            ],
            'visual_permissions' => $visualPermissions,
            'planned_markers' => [
                'inventory' => 'QA14A-P{clinic_id}-001..'.str_pad((string) $count, 3, '0', STR_PAD_LEFT),
                'billing' => 'QA14A-I{clinic_id}-001..'.str_pad((string) $count, 3, '0', STR_PAD_LEFT),
                'payments' => 'QA14A-Y{clinic_id}-001..'.str_pad((string) $count, 3, '0', STR_PAD_LEFT),
            ],
            'blocked_surfaces' => [
                'suppliers' => 'Ownership exists; legacy controller and views are not yet safe to publish.',
                'purchases' => 'Ownership exists; legacy controller and views are not yet safe to publish.',
                'quotes' => 'Ownership exists; legacy schema/controller mismatch is not yet safe to publish.',
            ],
        ];
    }

    /** @return array<string, mixed> */
    public function execute(string $clinicCode, string $actorEmail, int $count): array
    {
        $this->assertCount($count);
        $before = $this->transition->preview($clinicCode, $actorEmail);
        $this->assertTransitionReady($before);
        $visualPermissions = $this->visualPermissions($before);
        if (in_array(false, $visualPermissions, true)) {
            throw new RuntimeException('QA actor lacks required clinic permissions: '.implode(',', array_keys(array_filter(
                $visualPermissions,
                static fn (bool $allowed): bool => ! $allowed,
            ))));
        }

        if (! $this->readiness->isReady('inventory') || ! $this->readiness->isReady('billing')) {
            throw new RuntimeException('QA fixtures require ready inventory and billing ownership domains.');
        }

        $runId = (string) Str::uuid();

        return $this->connection->transaction(function () use ($before, $clinicCode, $actorEmail, $count, $runId): array {
            $clinicId = (int) $before['clinic']['id'];
            $actorId = (int) $before['actor']['id'];
            $now = now();
            $created = array_fill_keys([
                'patients', 'staff', 'cdt_catalog', 'inventory_locations', 'products',
                'inventory', 'inventory_movements', 'invoices', 'invoice_items', 'payments',
            ], 0);
            $ids = array_fill_keys(array_keys($created), []);

            $patientId = $this->supportPatient($clinicId, $actorId, $now, $created, $ids);
            $staffId = $this->supportStaff($clinicId, $actorId, $now, $created, $ids);
            $catalogId = $this->supportCatalog($clinicId, $now, $created, $ids);

            for ($number = 1; $number <= $count; $number++) {
                $suffix = str_pad((string) $number, 3, '0', STR_PAD_LEFT);
                $locationId = $this->location($clinicId, $suffix, $now, $created, $ids);
                $productId = $this->product($clinicId, $actorId, $suffix, $now, $created, $ids);
                $inventoryId = $this->inventory($clinicId, $productId, $locationId, $suffix, $now, $created, $ids);
                $this->movement($clinicId, $actorId, $productId, $inventoryId, $suffix, $now, $created, $ids);
                $invoiceId = $this->invoice($clinicId, $actorId, $patientId, $staffId, $suffix, $now, $created, $ids);
                $this->invoiceItem($invoiceId, $catalogId, $suffix, $now, $created, $ids);
                $this->payment($clinicId, $actorId, $patientId, $invoiceId, $suffix, $now, $created, $ids);
            }

            $after = $this->transition->preview($clinicCode, $actorEmail);
            $this->assertTransitionReady($after);
            if (! $this->readiness->isReady('inventory') || ! $this->readiness->isReady('billing')) {
                throw new RuntimeException('QA fixtures violated an owned-domain readiness contract.');
            }

            $summary = [
                'status' => 'executed',
                'run_id' => $runId,
                'clinic_code' => $clinicCode,
                'clinic_id' => $clinicId,
                'actor_id' => $actorId,
                'count_per_view' => $count,
                'created' => $created,
                'created_total' => array_sum($created),
                'ids' => $ids,
                'visible_counts' => [
                    'inventory' => $this->connection->table('inventory')->where('clinic_id', $clinicId)
                        ->whereIn('id', $ids['inventory'])->count(),
                    'billing' => $this->connection->table('invoices')->where('clinic_id', $clinicId)
                        ->whereIn('id', $ids['invoices'])->count(),
                    'payments' => $this->connection->table('payments')->where('clinic_id', $clinicId)
                        ->whereIn('id', $ids['payments'])->count(),
                ],
                'hashes_before' => $before['hashes'],
                'hashes_after' => $after['hashes'],
                'blocked_surfaces' => ['suppliers', 'purchases', 'quotes'],
            ];

            activity('qa')
                ->causedBy(\App\Models\User::query()->findOrFail($actorId))
                ->withProperties([
                    'run_id' => $summary['run_id'],
                    'clinic_id' => $summary['clinic_id'],
                    'created' => $summary['created'],
                    'ids' => $summary['ids'],
                ])
                ->log('clinic_owned_domains.qa_created');

            return $summary;
        }, 3);
    }

    private function supportPatient(int $clinicId, int $actorId, mixed $now, array &$created, array &$ids): int
    {
        $code = "QA14A-PAT-C{$clinicId}";
        $existing = $this->connection->table('patients')->where('patient_code', $code)->first(['id', 'clinic_id']);
        if ($existing !== null) {
            $this->assertOwner('patients', (int) $existing->id, $existing->clinic_id, $clinicId);
            $ids['patients'][] = (int) $existing->id;

            return (int) $existing->id;
        }

        $id = $this->connection->table('patients')->insertGetId([
            'clinic_id' => $clinicId,
            'patient_code' => $code,
            'first_name' => 'QA',
            'last_name' => 'PRUEBA Mandato 14A',
            'birth_date' => '1990-01-01',
            'gender' => 'other',
            'notes' => 'QA/PRUEBA: soporte exclusivo para validación visual del Mandato 14A.',
            'is_active' => true,
            'created_by' => $actorId,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $created['patients']++;
        $ids['patients'][] = $id;

        return $id;
    }

    private function supportStaff(int $clinicId, int $actorId, mixed $now, array &$created, array &$ids): int
    {
        $existing = $this->connection->table('staff')->where('clinic_id', $clinicId)->where('user_id', $actorId)->first(['id']);
        if ($existing !== null) {
            $ids['staff'][] = (int) $existing->id;

            return (int) $existing->id;
        }

        $id = $this->connection->table('staff')->insertGetId([
            'clinic_id' => $clinicId,
            'user_id' => $actorId,
            'employee_id' => "QA14A-STAFF-C{$clinicId}",
            'specialty' => 'QA/PRUEBA',
            'experience_years' => 0,
            'is_available' => true,
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $created['staff']++;
        $ids['staff'][] = $id;

        return $id;
    }

    private function supportCatalog(int $clinicId, mixed $now, array &$created, array &$ids): int
    {
        $code = "QA14A-C{$clinicId}";
        $existing = $this->connection->table('cdt_catalog')->where('cdt_code', $code)->first(['id']);
        if ($existing !== null) {
            $ids['cdt_catalog'][] = (int) $existing->id;

            return (int) $existing->id;
        }

        $id = $this->connection->table('cdt_catalog')->insertGetId([
            'cdt_code' => $code,
            'category' => 'QA/PRUEBA',
            'procedure_name' => 'QA/PRUEBA Procedimiento Mandato 14A',
            'description' => 'Registro técnico no clínico para validación visual.',
            'base_price' => 100,
            'difficulty_level' => 'basic',
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $created['cdt_catalog']++;
        $ids['cdt_catalog'][] = $id;

        return $id;
    }

    private function location(int $clinicId, string $suffix, mixed $now, array &$created, array &$ids): int
    {
        $code = "QA14A-L{$clinicId}-{$suffix}";
        $existing = $this->connection->table('inventory_locations')->where('code', $code)->first(['id', 'clinic_id']);
        if ($existing !== null) {
            $this->assertOwner('inventory_locations', (int) $existing->id, $existing->clinic_id, $clinicId);
            $ids['inventory_locations'][] = (int) $existing->id;

            return (int) $existing->id;
        }

        $id = $this->connection->table('inventory_locations')->insertGetId([
            'clinic_id' => $clinicId,
            'code' => $code,
            'name' => "QA/PRUEBA 14A Ubicación {$suffix} Clínica {$clinicId}",
            'type' => 'storage',
            'is_active' => true,
            'notes' => 'QA/PRUEBA: validación visual de inventario.',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $created['inventory_locations']++;
        $ids['inventory_locations'][] = $id;

        return $id;
    }

    private function product(int $clinicId, int $actorId, string $suffix, mixed $now, array &$created, array &$ids): int
    {
        $code = "QA14A-P{$clinicId}-{$suffix}";
        $existing = $this->connection->table('products')->where('product_code', $code)->first(['id', 'clinic_id']);
        if ($existing !== null) {
            $this->assertOwner('products', (int) $existing->id, $existing->clinic_id, $clinicId);
            $ids['products'][] = (int) $existing->id;

            return (int) $existing->id;
        }

        $id = $this->connection->table('products')->insertGetId([
            'clinic_id' => $clinicId,
            'product_code' => $code,
            'name' => "QA/PRUEBA 14A Insumo {$suffix}",
            'description' => 'QA/PRUEBA: producto técnico para validación visual.',
            'category' => 'materiales',
            'unit_of_measure' => 'piezas',
            'cost_price' => 10 + (int) $suffix,
            'selling_price' => 20 + (int) $suffix,
            'minimum_stock' => 2,
            'maximum_stock' => 50,
            'is_active' => true,
            'created_by' => $actorId,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $created['products']++;
        $ids['products'][] = $id;

        return $id;
    }

    private function inventory(int $clinicId, int $productId, int $locationId, string $suffix, mixed $now, array &$created, array &$ids): int
    {
        $existing = $this->connection->table('inventory')->where('product_id', $productId)
            ->where('inventory_location_id', $locationId)->first(['id', 'clinic_id']);
        if ($existing !== null) {
            $this->assertOwner('inventory', (int) $existing->id, $existing->clinic_id, $clinicId);
            $ids['inventory'][] = (int) $existing->id;

            return (int) $existing->id;
        }

        $stock = 10 + (int) $suffix;
        $id = $this->connection->table('inventory')->insertGetId([
            'clinic_id' => $clinicId,
            'product_id' => $productId,
            'inventory_location_id' => $locationId,
            'current_stock' => $stock,
            'reserved_stock' => 0,
            'available_stock' => $stock,
            'average_cost' => 10 + (int) $suffix,
            'last_restocked' => $now,
            'location' => "QA/PRUEBA 14A Ubicación {$suffix}",
            'notes' => 'QA/PRUEBA: inventario para validación visual.',
            'low_stock_alert' => false,
            'out_of_stock_alert' => false,
            'expiry_alert' => false,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $created['inventory']++;
        $ids['inventory'][] = $id;

        return $id;
    }

    private function movement(int $clinicId, int $actorId, int $productId, int $inventoryId, string $suffix, mixed $now, array &$created, array &$ids): void
    {
        $reason = "QA/PRUEBA Mandato 14A {$suffix}";
        $existing = $this->connection->table('inventory_movements')->where('inventory_id', $inventoryId)
            ->where('reason', $reason)->first(['id', 'clinic_id']);
        if ($existing !== null) {
            $this->assertOwner('inventory_movements', (int) $existing->id, $existing->clinic_id, $clinicId);
            $ids['inventory_movements'][] = (int) $existing->id;

            return;
        }

        $stock = 10 + (int) $suffix;
        $id = $this->connection->table('inventory_movements')->insertGetId([
            'clinic_id' => $clinicId,
            'inventory_id' => $inventoryId,
            'product_id' => $productId,
            'user_id' => $actorId,
            'type' => 'initial',
            'quantity' => $stock,
            'stock_before' => 0,
            'stock_after' => $stock,
            'destination_location' => "QA/PRUEBA 14A {$suffix}",
            'reason' => $reason,
            'metadata' => json_encode(['qa_marker' => 'MANDATE-14A'], JSON_THROW_ON_ERROR),
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $created['inventory_movements']++;
        $ids['inventory_movements'][] = $id;
    }

    private function invoice(int $clinicId, int $actorId, int $patientId, int $staffId, string $suffix, mixed $now, array &$created, array &$ids): int
    {
        $number = "QA14A-I{$clinicId}-{$suffix}";
        $existing = $this->connection->table('invoices')->where('invoice_number', $number)->first(['id', 'clinic_id']);
        if ($existing !== null) {
            $this->assertOwner('invoices', (int) $existing->id, $existing->clinic_id, $clinicId);
            $ids['invoices'][] = (int) $existing->id;

            return (int) $existing->id;
        }

        $total = 100 + (int) $suffix;
        $paid = 10 + (int) $suffix;
        $id = $this->connection->table('invoices')->insertGetId([
            'clinic_id' => $clinicId,
            'invoice_number' => $number,
            'patient_id' => $patientId,
            'staff_id' => $staffId,
            'invoice_date' => $now,
            'due_date' => now()->addDays(30),
            'status' => 'sent',
            'subtotal' => $total,
            'tax_rate' => 0,
            'tax_amount' => 0,
            'discount_amount' => 0,
            'total_amount' => $total,
            'paid_amount' => $paid,
            'balance_due' => $total - $paid,
            'notes' => "QA/PRUEBA Mandato 14A {$suffix}",
            'is_recurring' => false,
            'created_by' => $actorId,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $created['invoices']++;
        $ids['invoices'][] = $id;

        return $id;
    }

    private function invoiceItem(int $invoiceId, int $catalogId, string $suffix, mixed $now, array &$created, array &$ids): void
    {
        $existing = $this->connection->table('invoice_items')->where('invoice_id', $invoiceId)
            ->where('sequence_order', 1)->first(['id']);
        if ($existing !== null) {
            $ids['invoice_items'][] = (int) $existing->id;

            return;
        }

        $total = 100 + (int) $suffix;
        $id = $this->connection->table('invoice_items')->insertGetId([
            'invoice_id' => $invoiceId,
            'cdt_catalog_id' => $catalogId,
            'sequence_order' => 1,
            'item_name' => "QA/PRUEBA Servicio 14A {$suffix}",
            'description' => 'QA/PRUEBA: renglón para validación visual.',
            'quantity' => 1,
            'unit_price' => $total,
            'total_price' => $total,
            'tax_rate' => 0,
            'tax_amount' => 0,
            'notes' => 'QA/PRUEBA Mandato 14A',
            'is_taxable' => false,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $created['invoice_items']++;
        $ids['invoice_items'][] = $id;
    }

    private function payment(int $clinicId, int $actorId, int $patientId, int $invoiceId, string $suffix, mixed $now, array &$created, array &$ids): void
    {
        $number = "QA14A-Y{$clinicId}-{$suffix}";
        $existing = $this->connection->table('payments')->where('payment_number', $number)->first(['id', 'clinic_id']);
        if ($existing !== null) {
            $this->assertOwner('payments', (int) $existing->id, $existing->clinic_id, $clinicId);
            $ids['payments'][] = (int) $existing->id;

            return;
        }

        $id = $this->connection->table('payments')->insertGetId([
            'clinic_id' => $clinicId,
            'payment_number' => $number,
            'invoice_id' => $invoiceId,
            'patient_id' => $patientId,
            'payment_date' => $now,
            'amount' => 10 + (int) $suffix,
            'payment_method' => 'cash',
            'reference_number' => "QA14A-REF-{$suffix}",
            'notes' => "QA/PRUEBA Mandato 14A {$suffix}",
            'status' => 'completed',
            'processed_by' => $actorId,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $created['payments']++;
        $ids['payments'][] = $id;
    }

    private function assertCount(int $count): void
    {
        if ($count < 5 || $count > 25) {
            throw new RuntimeException('QA count must be between 5 and 25 records per view.');
        }
    }

    /** @param array<string, mixed> $transition */
    private function assertTransitionReady(array $transition): void
    {
        if ($transition['errors'] !== []) {
            throw new RuntimeException('QA fixtures blocked: '.implode(',', $transition['errors']));
        }
    }

    private function assertOwner(string $table, int $id, mixed $owner, int $clinicId): void
    {
        if ($owner === null || (int) $owner !== $clinicId) {
            throw new RuntimeException("QA marker collision in {$table}#{$id}.");
        }
    }

    /** @param array<string, mixed> $transition @return array<string, bool> */
    private function visualPermissions(array $transition): array
    {
        $required = [
            'view_inventory',
            'manage_inventory',
            'view_billing',
            'manage_billing',
            'view_payments',
            'manage_payments',
        ];
        $result = array_fill_keys($required, false);

        if (($transition['actor']['id'] ?? null) === null
            || ($transition['clinic']['id'] ?? null) === null
            || ($transition['membership_id'] ?? null) === null) {
            return $result;
        }

        $user = \App\Models\User::query()->find((int) $transition['actor']['id']);
        if ($user === null) {
            return $result;
        }

        $context = new \App\Modules\Clinics\Data\ClinicContext(
            (int) $transition['actor']['id'],
            (int) $transition['clinic']['id'],
            (int) $transition['membership_id'],
        );
        foreach ($required as $permission) {
            $result[$permission] = $this->permissions->allows($user, $permission, $context);
        }

        return $result;
    }
}
