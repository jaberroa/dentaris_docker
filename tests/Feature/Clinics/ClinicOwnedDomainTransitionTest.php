<?php

namespace Tests\Feature\Clinics;

use App\Models\Inventory;
use App\Models\InventoryLocation;
use App\Models\InventoryMovement;
use App\Models\Invoice;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Staff;
use App\Models\User;
use App\Modules\Clinics\Data\ClinicContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\InteractsWithClinicalContext;
use Tests\TestCase;

class ClinicOwnedDomainTransitionTest extends TestCase
{
    use InteractsWithClinicalContext;
    use RefreshDatabase;

    public function test_dry_run_reports_ambiguous_locations_without_modifying_them(): void
    {
        [$user] = $this->actor();
        $now = now();
        $locationId = DB::table('inventory_locations')->insertGetId([
            'code' => 'LEGACY-LOC-'.uniqid(),
            'name' => 'Ubicación heredada ambigua',
            'type' => 'storage',
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $exitCode = Artisan::call('clinics:transition-owned-domains', [
            '--actor-email' => $user->email,
        ]);
        $output = Artisan::output();

        $this->assertSame(0, $exitCode);
        $this->assertNull(DB::table('inventory_locations')->where('id', $locationId)->value('clinic_id'));
        $this->assertStringContainsString('inventory_locations_have_no_unambiguous_parent', $output);
        $this->assertStringContainsString('"status":"dry_run"', $output);
    }

    public function test_evidence_based_transition_is_atomic_and_idempotent(): void
    {
        [$user, $context] = $this->actor();
        $fixture = $this->legacyFixture($user, $context);
        $timestamps = [
            'inventory' => DB::table('inventory')->where('id', $fixture['inventory'])->value('updated_at'),
            'movement' => DB::table('inventory_movements')->where('id', $fixture['movement'])->value('updated_at'),
            'invoice' => DB::table('invoices')->where('id', $fixture['invoice'])->value('updated_at'),
            'payment' => DB::table('payments')->where('id', $fixture['payment'])->value('updated_at'),
        ];

        $first = Artisan::call('clinics:transition-owned-domains', [
            '--actor-email' => $user->email,
            '--execute' => true,
        ]);
        $firstOutput = Artisan::output();

        $this->assertSame(0, $first, $firstOutput);
        $this->assertSame($context->clinicId, (int) Inventory::query()->findOrFail($fixture['inventory'])->clinic_id);
        $this->assertSame($context->clinicId, (int) InventoryMovement::query()->findOrFail($fixture['movement'])->clinic_id);
        $this->assertSame($context->clinicId, (int) Invoice::query()->findOrFail($fixture['invoice'])->clinic_id);
        $this->assertSame($context->clinicId, (int) Payment::query()->findOrFail($fixture['payment'])->clinic_id);
        $this->assertStringContainsString('"updated_total":4', $firstOutput, $firstOutput);
        $this->assertSame($timestamps['inventory'], DB::table('inventory')->where('id', $fixture['inventory'])->value('updated_at'));
        $this->assertSame($timestamps['movement'], DB::table('inventory_movements')->where('id', $fixture['movement'])->value('updated_at'));
        $this->assertSame($timestamps['invoice'], DB::table('invoices')->where('id', $fixture['invoice'])->value('updated_at'));
        $this->assertSame($timestamps['payment'], DB::table('payments')->where('id', $fixture['payment'])->value('updated_at'));

        $second = app(\App\Modules\Clinics\Services\ClinicOwnedDomainTransitionService::class)
            ->execute('DEN-CL-001', $user->email);

        $this->assertSame(0, $second['updated_total']);
        $this->assertSame(2, DB::table('activity_log')->where('description', 'clinic_owned_domains.transitioned')->count());
    }

    public function test_execute_rolls_back_all_candidates_when_any_pending_row_is_ambiguous(): void
    {
        [$user, $context] = $this->actor();
        $fixture = $this->legacyFixture($user, $context);
        $now = now();
        DB::table('inventory_locations')->insert([
            'code' => 'AMBIGUOUS-'.uniqid(),
            'name' => 'Ubicación sin evidencia suficiente',
            'type' => 'storage',
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $exit = Artisan::call('clinics:transition-owned-domains', [
            '--actor-email' => $user->email,
            '--execute' => true,
        ]);

        $this->assertSame(1, $exit);
        $this->assertStringContainsString('inventory_locations_have_no_unambiguous_parent', Artisan::output());
        $this->assertNull(DB::table('inventory')->where('id', $fixture['inventory'])->value('clinic_id'));
        $this->assertNull(DB::table('inventory_movements')->where('id', $fixture['movement'])->value('clinic_id'));
        $this->assertNull(DB::table('invoices')->where('id', $fixture['invoice'])->value('clinic_id'));
        $this->assertNull(DB::table('payments')->where('id', $fixture['payment'])->value('clinic_id'));
        $this->assertSame(0, DB::table('activity_log')->where('description', 'clinic_owned_domains.transitioned')->count());
    }

    /** @return array{User, ClinicContext} */
    private function actor(): array
    {
        $user = User::factory()->create(['is_active' => true]);
        $context = $this->clinicalContextFor($user, ['manage_inventory', 'manage_billing']);
        DB::table('clinics')->where('id', $context->clinicId)->update(['code' => 'DEN-CL-001']);

        return [$user, $context];
    }

    /** @return array<string, int> */
    private function legacyFixture(User $user, ClinicContext $context): array
    {
        $now = now();
        $location = new InventoryLocation([
            'code' => 'TRANSITION-LOC-'.uniqid(),
            'name' => 'Ubicación con evidencia',
            'type' => 'storage',
            'is_active' => true,
        ]);
        $location->forceFill(['clinic_id' => $context->clinicId])->save();

        $product = Product::query()->create([
            'product_code' => 'TRANSITION-PROD-'.uniqid(),
            'name' => 'Producto heredado',
            'category' => 'materiales',
            'unit_of_measure' => 'piezas',
            'minimum_stock' => 1,
            'created_by' => $user->id,
        ]);
        $inventory = Inventory::query()->create([
            'product_id' => $product->id,
            'inventory_location_id' => $location->id,
            'current_stock' => 5,
            'available_stock' => 5,
        ]);
        $movement = InventoryMovement::query()->create([
            'inventory_id' => $inventory->id,
            'product_id' => $product->id,
            'user_id' => $user->id,
            'type' => 'initial',
            'quantity' => 5,
            'stock_before' => 0,
            'stock_after' => 5,
            'reason' => 'Evidencia de transición',
        ]);

        $patient = new Patient([
            'patient_code' => 'TRANSITION-PAT-'.uniqid(),
            'first_name' => 'QA',
            'last_name' => 'Transición',
            'birth_date' => '1990-01-01',
            'gender' => 'other',
            'created_by' => $user->id,
        ]);
        $patient->forceFill(['clinic_id' => $context->clinicId])->save();
        $staff = new Staff([
            'user_id' => $user->id,
            'employee_id' => 'TRANSITION-STAFF-'.uniqid(),
            'is_active' => true,
        ]);
        $staff->forceFill(['clinic_id' => $context->clinicId])->save();
        $invoice = Invoice::query()->create([
            'invoice_number' => 'TRANSITION-INV-'.uniqid(),
            'patient_id' => $patient->id,
            'staff_id' => $staff->id,
            'invoice_date' => '2026-09-04',
            'status' => 'sent',
            'total_amount' => 100,
            'balance_due' => 90,
            'created_by' => $user->id,
        ]);
        $payment = Payment::query()->create([
            'payment_number' => 'TRANSITION-PAY-'.uniqid(),
            'invoice_id' => $invoice->id,
            'patient_id' => $patient->id,
            'payment_date' => '2026-09-04',
            'amount' => 10,
            'payment_method' => 'cash',
            'status' => 'completed',
            'processed_by' => $user->id,
        ]);

        return [
            'inventory' => $inventory->id,
            'movement' => $movement->id,
            'invoice' => $invoice->id,
            'payment' => $payment->id,
        ];
    }
}
