<?php

namespace Tests\Feature\Clinics;

use App\Models\Patient;
use App\Models\Staff;
use App\Models\User;
use App\Modules\Clinics\Data\ClinicContext;
use App\Modules\Clinics\Services\ClinicOperationalDomainTransitionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\InteractsWithClinicalContext;
use Tests\TestCase;

class ClinicOperationalDomainTransitionTest extends TestCase
{
    use InteractsWithClinicalContext;
    use RefreshDatabase;

    public function test_dry_run_then_evidence_based_execution_is_atomic_audited_and_idempotent(): void
    {
        [$user, $context] = $this->actor();
        $fixture = $this->legacyOperationalFixture($user, $context);
        $timestamps = collect($fixture)->mapWithKeys(
            fn (int $id, string $table): array => [$table => DB::table($table)->where('id', $id)->value('updated_at')],
        )->all();

        $dryRun = Artisan::call('clinics:transition-operational-domains', [
            '--actor-id' => $user->id,
        ]);
        $preview = app(ClinicOperationalDomainTransitionService::class)
            ->preview('DEN-CL-001', $user->id);

        $this->assertSame(0, $dryRun, Artisan::output());
        $this->assertSame([], $preview['errors']);
        $this->assertSame([1, 1, 1, 1], array_values(array_map('count', $preview['assignments'])));
        foreach ($fixture as $table => $id) {
            $this->assertNull(DB::table($table)->where('id', $id)->value('clinic_id'));
        }

        $first = app(ClinicOperationalDomainTransitionService::class)
            ->execute('DEN-CL-001', $user->id);

        $this->assertSame(4, $first['updated_total']);
        $this->assertSame($first['before']['hashes']['integrity'], $first['after']['hashes']['integrity']);
        foreach ($fixture as $table => $id) {
            $this->assertSame($context->clinicId, (int) DB::table($table)->where('id', $id)->value('clinic_id'));
            $this->assertSame($timestamps[$table], DB::table($table)->where('id', $id)->value('updated_at'));
        }

        $second = app(ClinicOperationalDomainTransitionService::class)
            ->execute('DEN-CL-001', $user->id);

        $this->assertSame(0, $second['updated_total']);
        $this->assertSame(2, DB::table('activity_log')
            ->where('description', 'clinic_operational_domains.transitioned')->count());
    }

    public function test_execute_rolls_back_every_candidate_when_product_evidence_crosses_clinics(): void
    {
        [$user, $context] = $this->actor();
        $fixture = $this->legacyOperationalFixture($user, $context);
        $otherClinicId = DB::table('clinics')->insertGetId([
            'code' => 'DEN-CL-002',
            'name' => 'Clínica secundaria',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $otherLocationId = DB::table('inventory_locations')->insertGetId([
            'clinic_id' => $otherClinicId,
            'code' => 'CROSS-LOC',
            'name' => 'Ubicación cruzada',
            'type' => 'storage',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('inventory')->insert([
            'clinic_id' => $otherClinicId,
            'product_id' => $fixture['products'],
            'inventory_location_id' => $otherLocationId,
            'current_stock' => 1,
            'available_stock' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $exit = Artisan::call('clinics:transition-operational-domains', [
            '--actor-id' => $user->id,
            '--execute' => true,
        ]);

        $this->assertSame(1, $exit);
        $this->assertStringContainsString('ambiguous_or_foreign', Artisan::output());
        foreach ($fixture as $table => $id) {
            $this->assertNull(DB::table($table)->where('id', $id)->value('clinic_id'));
        }
        $this->assertSame(0, DB::table('activity_log')
            ->where('description', 'clinic_operational_domains.transitioned')->count());
    }

    public function test_global_role_never_authorizes_transition_without_active_membership_permission(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $context = $this->clinicalContextFor($user, [], membershipOverrides: ['suspended_at' => now()]);
        DB::table('clinics')->where('id', $context->clinicId)->update(['code' => 'DEN-CL-001']);
        $roleId = DB::table('roles')->insertGetId([
            'name' => 'global-admin-'.uniqid(),
            'display_name' => 'Global no clínico',
            'permissions' => json_encode(['manage_inventory', 'manage_suppliers', 'manage_purchases', 'manage_quotes'], JSON_THROW_ON_ERROR),
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('role_user')->insert(['role_id' => $roleId, 'user_id' => $user->id]);

        $exit = Artisan::call('clinics:transition-operational-domains', [
            '--actor-id' => $user->id,
            '--execute' => true,
        ]);

        $this->assertSame(1, $exit);
        $this->assertStringContainsString('actor_requires_active_membership', Artisan::output());
    }

    /** @return array{User, ClinicContext} */
    private function actor(): array
    {
        $user = User::factory()->create(['is_active' => true]);
        $context = $this->clinicalContextFor($user, [
            'manage_inventory', 'manage_suppliers', 'manage_purchases', 'manage_quotes',
        ]);
        DB::table('clinics')->where('id', $context->clinicId)->update(['code' => 'DEN-CL-001']);

        return [$user, $context];
    }

    /** @return array{suppliers: int, products: int, purchases: int, quotes: int} */
    private function legacyOperationalFixture(User $user, ClinicContext $context): array
    {
        $now = now();
        $supplierId = DB::table('suppliers')->insertGetId([
            'supplier_code' => 'LEG-SUP-'.uniqid(),
            'company_name' => 'Proveedor con evidencia',
            'created_by' => $user->id,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $productId = DB::table('products')->insertGetId([
            'product_code' => 'LEG-PROD-'.uniqid(),
            'name' => 'Producto con evidencia',
            'category' => 'materiales',
            'unit_of_measure' => 'piezas',
            'minimum_stock' => 1,
            'primary_supplier_id' => $supplierId,
            'created_by' => $user->id,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $locationId = DB::table('inventory_locations')->insertGetId([
            'clinic_id' => $context->clinicId,
            'code' => 'LEG-LOC-'.uniqid(),
            'name' => 'Ubicación con evidencia',
            'type' => 'storage',
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        DB::table('inventory')->insert([
            'clinic_id' => $context->clinicId,
            'product_id' => $productId,
            'inventory_location_id' => $locationId,
            'current_stock' => 5,
            'available_stock' => 5,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $purchaseId = DB::table('purchases')->insertGetId([
            'purchase_number' => 'LEG-PUR-'.uniqid(),
            'supplier_id' => $supplierId,
            'purchase_date' => '2026-09-05',
            'created_by' => $user->id,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        DB::table('purchase_items')->insert([
            'purchase_id' => $purchaseId,
            'product_id' => $productId,
            'quantity_ordered' => 1,
            'unit_cost' => 10,
            'total_cost' => 10,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $patient = Patient::factory()->forClinic($context)->create(['created_by' => $user->id]);
        $professional = Staff::factory()->forClinic($context)->create();
        $quoteId = DB::table('quotes')->insertGetId([
            'quote_number' => 'LEG-QUOTE-'.uniqid(),
            'patient_id' => $patient->id,
            'staff_id' => $professional->id,
            'quote_date' => '2026-09-05',
            'created_by' => $user->id,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $catalogId = DB::table('cdt_catalog')->insertGetId([
            'cdt_code' => 'LEG-CDT-'.uniqid(),
            'category' => 'Prueba',
            'procedure_name' => 'Procedimiento de prueba',
            'base_price' => 10,
            'difficulty_level' => 'basic',
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        DB::table('quote_items')->insert([
            'quote_id' => $quoteId,
            'cdt_catalog_id' => $catalogId,
            'item_name' => 'Renglón de prueba',
            'unit_price' => 10,
            'total_price' => 10,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return [
            'suppliers' => $supplierId,
            'products' => $productId,
            'purchases' => $purchaseId,
            'quotes' => $quoteId,
        ];
    }
}
