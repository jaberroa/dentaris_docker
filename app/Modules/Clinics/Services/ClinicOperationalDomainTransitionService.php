<?php

namespace App\Modules\Clinics\Services;

use App\Models\User;
use App\Modules\Clinics\Data\ClinicContext;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Assigns operational ownership only when existing relations prove one clinic.
 *
 * Products derive their owner from owned inventory. Suppliers derive it from
 * their resolved products (or an already-owned purchase), purchases from their
 * supplier and every item product, and quotes from matching patient/staff data.
 */
final class ClinicOperationalDomainTransitionService
{
    /** @var list<string> */
    private const OWNED_TABLES = ['suppliers', 'products', 'purchases', 'quotes'];

    /** @var list<string> */
    private const HASH_TABLES = [
        'suppliers', 'products', 'purchases', 'purchase_items', 'quotes', 'quote_items',
    ];

    /** @var array<string, string> */
    private const TABLE_PERMISSIONS = [
        'suppliers' => 'manage_suppliers',
        'products' => 'manage_inventory',
        'purchases' => 'manage_purchases',
        'quotes' => 'manage_quotes',
    ];

    public function __construct(
        private readonly ConnectionInterface $connection,
        private readonly ClinicPermissionService $permissions,
    ) {}

    /** @return array<string, mixed> */
    public function preview(string $clinicCode, int $actorId): array
    {
        return $this->inspect($clinicCode, $actorId);
    }

    /** @return array<string, mixed> */
    public function execute(string $clinicCode, int $actorId): array
    {
        $runId = (string) Str::uuid();

        return $this->connection->transaction(function () use ($clinicCode, $actorId, $runId): array {
            $before = $this->inspect($clinicCode, $actorId, true);
            $this->assertExecutable($before);

            $targetClinicId = (int) $before['clinic']['id'];
            $updated = [];
            foreach (self::OWNED_TABLES as $table) {
                $ids = $before['assignments'][$table];
                $updated[$table] = $ids === [] ? 0 : $this->connection->table($table)
                    ->whereNull('clinic_id')
                    ->whereIn('id', $ids)
                    ->update(['clinic_id' => $targetClinicId]);
            }

            $after = $this->inspect($clinicCode, $actorId);
            $this->assertExecutable($after);

            foreach ($after['tables'] as $table => $counts) {
                if ($counts['pending'] !== 0) {
                    throw new RuntimeException("Operational transition left pending rows in {$table}.");
                }
            }

            if ($before['hashes']['integrity'] !== $after['hashes']['integrity']) {
                throw new RuntimeException('Operational transition changed data outside clinic_id.');
            }

            $summary = [
                'status' => 'executed',
                'run_id' => $runId,
                'clinic_code' => $clinicCode,
                'clinic_id' => $targetClinicId,
                'actor_id' => $actorId,
                'updated' => $updated,
                'updated_total' => array_sum($updated),
                'before' => $this->summary($before),
                'after' => $this->summary($after),
            ];

            activity('security')
                ->causedBy(User::query()->findOrFail($actorId))
                ->withProperties([
                    'run_id' => $runId,
                    'clinic_id' => $targetClinicId,
                    'updated' => $updated,
                    'before_hashes' => $summary['before']['hashes'],
                    'after_hashes' => $summary['after']['hashes'],
                ])
                ->log('clinic_operational_domains.transitioned');

            return $summary;
        }, 3);
    }

    /** @return array<string, mixed> */
    private function inspect(string $clinicCode, int $actorId, bool $lock = false): array
    {
        $errors = [];
        $schema = [];
        $schemaBuilder = $this->connection->getSchemaBuilder();

        foreach (self::OWNED_TABLES as $table) {
            $schema[$table] = $schemaBuilder->hasTable($table)
                && $schemaBuilder->hasColumn($table, 'clinic_id');
        }

        if (in_array(false, $schema, true)) {
            $errors[] = 'missing_operational_clinic_ownership_schema';
        }

        $clinics = $this->connection->table('clinics')->where('code', $clinicCode)
            ->get(['id', 'code', 'is_active']);
        $clinic = $clinics->first();
        if ($clinics->count() !== 1) {
            $errors[] = 'target_clinic_must_exist_exactly_once';
        } elseif (! (bool) $clinic->is_active) {
            $errors[] = 'target_clinic_must_be_active';
        }

        $actor = $this->connection->table('users')->where('id', $actorId)->first(['id', 'is_active']);
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

        if (in_array(false, $schema, true) || $clinic === null) {
            return [
                'clinic' => $clinic === null ? null : (array) $clinic,
                'actor' => $actor === null ? null : (array) $actor,
                'membership_id' => $membership?->id,
                'schema' => $schema,
                'permissions' => [],
                'tables' => $this->tableCountsWithoutOwnership(),
                'assignments' => array_fill_keys(self::OWNED_TABLES, []),
                'relations' => [],
                'hashes' => ['integrity' => $this->hashes(false), 'ownership' => $this->hashes(true)],
                'errors' => array_values(array_unique($errors)),
            ];
        }

        if ($lock) {
            foreach ([...self::OWNED_TABLES, 'inventory', 'purchase_items', 'patients', 'staff', 'treatment_plans', 'quote_items'] as $table) {
                $this->connection->table($table)->orderBy('id')->lockForUpdate()->get(['id']);
            }
        }

        $targetClinicId = (int) $clinic->id;
        $plan = $this->ownershipPlan($targetClinicId);
        $tables = $this->tableCountsWithOwnership($targetClinicId, $plan['assignments']);
        foreach ($tables as $table => $counts) {
            if ($counts['pending'] !== $counts['candidates']) {
                $errors[] = $table.'_has_ambiguous_or_foreign_pending_rows';
            }
        }

        if (array_sum($plan['relations']) > 0) {
            $errors[] = 'cross_clinic_or_broken_operational_relations_detected';
        }

        $permissionState = [];
        if ($actor !== null && $membership !== null) {
            $context = new ClinicContext((int) $actor->id, $targetClinicId, (int) $membership->id);
            $user = User::query()->find((int) $actor->id);
            foreach (self::TABLE_PERMISSIONS as $table => $permission) {
                if ($tables[$table]['total'] === 0) {
                    continue;
                }
                $permissionState[$permission] = $user !== null
                    && $this->permissions->allows($user, $permission, $context);
                if (! $permissionState[$permission]) {
                    $errors[] = 'actor_missing_'.$permission;
                }
            }
        }

        return [
            'clinic' => (array) $clinic,
            'actor' => $actor === null ? null : (array) $actor,
            'membership_id' => $membership?->id,
            'schema' => $schema,
            'permissions' => $permissionState,
            'tables' => $tables,
            'assignments' => $plan['assignments'],
            'relations' => $plan['relations'],
            'hashes' => ['integrity' => $this->hashes(false), 'ownership' => $this->hashes(true)],
            'errors' => array_values(array_unique($errors)),
        ];
    }

    /** @return array{assignments: array<string, list<int>>, relations: array<string, int>} */
    private function ownershipPlan(int $targetClinicId): array
    {
        $suppliers = $this->connection->table('suppliers')->get(['id', 'clinic_id'])->keyBy('id');
        $products = $this->connection->table('products')->get(['id', 'clinic_id', 'primary_supplier_id'])->keyBy('id');
        $inventories = $this->connection->table('inventory')->get(['id', 'clinic_id', 'product_id']);
        $purchases = $this->connection->table('purchases')->get(['id', 'clinic_id', 'supplier_id'])->keyBy('id');
        $purchaseItems = $this->connection->table('purchase_items')->get(['id', 'purchase_id', 'product_id']);
        $quotes = $this->connection->table('quotes')->get(['id', 'clinic_id', 'patient_id', 'staff_id', 'treatment_plan_id'])->keyBy('id');
        $patients = $this->connection->table('patients')->get(['id', 'clinic_id'])->keyBy('id');
        $staff = $this->connection->table('staff')->get(['id', 'clinic_id'])->keyBy('id');
        $plans = $this->connection->table('treatment_plans')->get(['id', 'patient_id', 'staff_id'])->keyBy('id');
        $quoteItems = $this->connection->table('quote_items')->get(['id', 'quote_id', 'cdt_catalog_id']);
        $catalogIds = $this->connection->table('cdt_catalog')->pluck('id')->mapWithKeys(
            static fn (mixed $id): array => [(int) $id => true],
        );

        $resolvedProducts = [];
        foreach ($products as $product) {
            $owner = $this->owner($product->clinic_id);
            if ($owner === null) {
                $evidence = $inventories->where('product_id', $product->id);
                $owner = $this->derivedOwner($evidence->pluck('clinic_id'), $evidence->isNotEmpty());
            }
            $resolvedProducts[(int) $product->id] = $owner;
        }

        $resolvedSuppliers = [];
        foreach ($suppliers as $supplier) {
            $owner = $this->owner($supplier->clinic_id);
            if ($owner === null) {
                $productEvidence = $products->where('primary_supplier_id', $supplier->id)
                    ->map(fn (object $product): ?int => $resolvedProducts[(int) $product->id]);
                $ownedPurchaseEvidence = $purchases->where('supplier_id', $supplier->id)
                    ->pluck('clinic_id')->filter(static fn (mixed $id): bool => $id !== null);
                $evidence = $productEvidence->concat($ownedPurchaseEvidence);
                $owner = $this->derivedOwner($evidence, $evidence->isNotEmpty());
            }
            $resolvedSuppliers[(int) $supplier->id] = $owner;
        }

        $resolvedPurchases = [];
        foreach ($purchases as $purchase) {
            $owner = $this->owner($purchase->clinic_id);
            if ($owner === null) {
                $supplierOwner = $resolvedSuppliers[(int) $purchase->supplier_id] ?? null;
                $items = $purchaseItems->where('purchase_id', $purchase->id);
                $productOwners = $items->map(
                    fn (object $item): ?int => $resolvedProducts[(int) $item->product_id] ?? null,
                );
                $complete = $supplierOwner !== null && ! $productOwners->contains(null);
                $owner = $this->derivedOwner(collect([$supplierOwner])->concat($productOwners), $complete);
            }
            $resolvedPurchases[(int) $purchase->id] = $owner;
        }

        $resolvedQuotes = [];
        foreach ($quotes as $quote) {
            $owner = $this->owner($quote->clinic_id);
            if ($owner === null) {
                $patientOwner = $this->owner($patients->get($quote->patient_id)?->clinic_id);
                $staffOwner = $this->owner($staff->get($quote->staff_id)?->clinic_id);
                $planMatches = $quote->treatment_plan_id === null
                    || (($plan = $plans->get($quote->treatment_plan_id)) !== null
                        && (int) $plan->patient_id === (int) $quote->patient_id
                        && (int) $plan->staff_id === (int) $quote->staff_id);
                $owner = $this->derivedOwner(collect([$patientOwner, $staffOwner]), $planMatches);
            }
            $resolvedQuotes[(int) $quote->id] = $owner;
        }

        $assignments = [
            'suppliers' => $this->assignments($suppliers, $resolvedSuppliers, $targetClinicId),
            'products' => $this->assignments($products, $resolvedProducts, $targetClinicId),
            'purchases' => $this->assignments($purchases, $resolvedPurchases, $targetClinicId),
            'quotes' => $this->assignments($quotes, $resolvedQuotes, $targetClinicId),
        ];

        $relations = [
            'inventory_product_mismatch' => $inventories->filter(function (object $inventory) use ($products, $resolvedProducts): bool {
                $product = $products->get($inventory->product_id);
                $inventoryOwner = $this->owner($inventory->clinic_id);
                $productOwner = $resolvedProducts[(int) $inventory->product_id] ?? null;

                return $product === null || $inventoryOwner === null || $productOwner === null || $inventoryOwner !== $productOwner;
            })->count(),
            'product_supplier_mismatch' => $products->filter(function (object $product) use ($suppliers, $resolvedProducts, $resolvedSuppliers): bool {
                if ($product->primary_supplier_id === null) {
                    return false;
                }

                return $suppliers->get($product->primary_supplier_id) === null
                    || ($resolvedProducts[(int) $product->id] ?? null) === null
                    || ($resolvedSuppliers[(int) $product->primary_supplier_id] ?? null) === null
                    || $resolvedProducts[(int) $product->id] !== $resolvedSuppliers[(int) $product->primary_supplier_id];
            })->count(),
            'purchase_supplier_or_item_mismatch' => $purchases->filter(function (object $purchase) use (
                $suppliers, $products, $purchaseItems, $resolvedPurchases, $resolvedSuppliers, $resolvedProducts,
            ): bool {
                $purchaseOwner = $resolvedPurchases[(int) $purchase->id] ?? null;
                $supplierOwner = $resolvedSuppliers[(int) $purchase->supplier_id] ?? null;
                if ($suppliers->get($purchase->supplier_id) === null || $purchaseOwner === null || $purchaseOwner !== $supplierOwner) {
                    return true;
                }

                return $purchaseItems->where('purchase_id', $purchase->id)->contains(function (object $item) use ($products, $resolvedProducts, $purchaseOwner): bool {
                    return $products->get($item->product_id) === null
                        || ($resolvedProducts[(int) $item->product_id] ?? null) !== $purchaseOwner;
                });
            })->count(),
            'orphan_purchase_items' => $purchaseItems->filter(
                fn (object $item): bool => $purchases->get($item->purchase_id) === null || $products->get($item->product_id) === null,
            )->count(),
            'quote_clinical_relation_mismatch' => $quotes->filter(function (object $quote) use ($patients, $staff, $plans, $resolvedQuotes): bool {
                $owner = $resolvedQuotes[(int) $quote->id] ?? null;
                $patient = $patients->get($quote->patient_id);
                $professional = $staff->get($quote->staff_id);
                if ($owner === null || $this->owner($patient?->clinic_id) !== $owner || $this->owner($professional?->clinic_id) !== $owner) {
                    return true;
                }
                if ($quote->treatment_plan_id === null) {
                    return false;
                }

                $plan = $plans->get($quote->treatment_plan_id);

                return $plan === null
                    || (int) $plan->patient_id !== (int) $quote->patient_id
                    || (int) $plan->staff_id !== (int) $quote->staff_id;
            })->count(),
            'orphan_quote_items' => $quoteItems->filter(
                fn (object $item): bool => $quotes->get($item->quote_id) === null || ! $catalogIds->has((int) $item->cdt_catalog_id),
            )->count(),
        ];

        return compact('assignments', 'relations');
    }

    private function derivedOwner(Collection $evidence, bool $complete): ?int
    {
        if (! $complete || $evidence->contains(null)) {
            return null;
        }

        $owners = $evidence->map(fn (mixed $id): ?int => $this->owner($id))->filter()->unique()->values();

        return $owners->count() === 1 ? (int) $owners->first() : null;
    }

    private function owner(mixed $value): ?int
    {
        return $value === null ? null : (int) $value;
    }

    /** @param Collection<int, object> $rows @param array<int, int|null> $resolved @return list<int> */
    private function assignments(Collection $rows, array $resolved, int $targetClinicId): array
    {
        return $rows->filter(
            fn (object $row): bool => $row->clinic_id === null && ($resolved[(int) $row->id] ?? null) === $targetClinicId,
        )->pluck('id')->map(static fn (mixed $id): int => (int) $id)->values()->all();
    }

    /** @param array<string, list<int>> $assignments @return array<string, array<string, int>> */
    private function tableCountsWithOwnership(int $clinicId, array $assignments): array
    {
        $result = [];
        foreach (self::OWNED_TABLES as $table) {
            $result[$table] = [
                'total' => $this->connection->table($table)->count(),
                'pending' => $this->connection->table($table)->whereNull('clinic_id')->count(),
                'target' => $this->connection->table($table)->where('clinic_id', $clinicId)->count(),
                'other' => $this->connection->table($table)->whereNotNull('clinic_id')->where('clinic_id', '<>', $clinicId)->count(),
                'candidates' => count($assignments[$table]),
            ];
        }

        return $result;
    }

    /** @return array<string, array<string, int|null>> */
    private function tableCountsWithoutOwnership(): array
    {
        $result = [];
        foreach (self::OWNED_TABLES as $table) {
            $result[$table] = [
                'total' => $this->connection->getSchemaBuilder()->hasTable($table)
                    ? $this->connection->table($table)->count() : null,
                'pending' => null,
                'target' => null,
                'other' => null,
                'candidates' => null,
            ];
        }

        return $result;
    }

    /** @return array<string, string|null> */
    private function hashes(bool $withOwnership): array
    {
        $hashes = [];
        $schema = $this->connection->getSchemaBuilder();
        foreach (self::HASH_TABLES as $table) {
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

    /** @param array<string, mixed> $state */
    private function assertExecutable(array $state): void
    {
        if ($state['errors'] !== []) {
            throw new RuntimeException('Operational transition blocked: '.implode(',', $state['errors']));
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
