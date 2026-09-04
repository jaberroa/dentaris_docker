<?php

namespace Tests\Feature\Clinics;

use App\Models\CdtCatalog;
use App\Models\Inventory;
use App\Models\InventoryLocation;
use App\Models\Invoice;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Role;
use App\Models\Staff;
use App\Models\User;
use App\Modules\Clinics\Data\ClinicContext;
use App\Services\CacheService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithClinicalContext;
use Tests\TestCase;

class InventoryBillingClinicalIsolationTest extends TestCase
{
    use InteractsWithClinicalContext;
    use RefreshDatabase;

    public function test_inventory_and_billing_lists_searches_details_exports_and_payments_are_isolated(): void
    {
        $fixture = $this->fixture();
        $this->actingAs($fixture['user'])->withSession(['clinic_id' => $fixture['context_a']->clinicId]);

        $this->get(route('inventory.index'))
            ->assertOk()
            ->assertSee('Material clínica A')
            ->assertDontSee('Material clínica B');

        $this->get(route('inventory.index', ['search' => 'Material clínica B']))
            ->assertOk()
            ->assertViewHas('inventories', fn ($inventories) => $inventories->total() === 0);

        $this->get(route('inventory.show', $fixture['inventory_b']))->assertNotFound();

        $export = $this->post(route('inventory.export'), ['format' => 'csv']);
        $export->assertOk();
        $csv = $export->streamedContent();
        $this->assertStringContainsString('Material clínica A', $csv);
        $this->assertStringNotContainsString('Material clínica B', $csv);

        $this->get(route('billing.index'))
            ->assertOk()
            ->assertSee($fixture['invoice_a']->invoice_number)
            ->assertDontSee($fixture['invoice_b']->invoice_number);

        $this->get(route('billing.index', ['search' => $fixture['invoice_b']->invoice_number]))
            ->assertOk()
            ->assertViewHas('invoices', fn ($invoices) => $invoices->total() === 0);

        $this->get(route('billing.show', $fixture['invoice_b']))->assertNotFound();

        $this->get(route('payments.index'))
            ->assertOk()
            ->assertSee('11.11')
            ->assertDontSee('99.99');

        $this->get(route('payments.show', $fixture['payment_b']))->assertNotFound();
    }

    public function test_submitted_clinic_and_cross_clinic_relationship_ids_are_rejected(): void
    {
        $fixture = $this->fixture();
        $this->actingAs($fixture['user'])->withSession(['clinic_id' => $fixture['context_a']->clinicId]);

        $this->from(route('inventory.locations.index'))
            ->post(route('inventory.locations.store'), [
                'clinic_id' => $fixture['context_b']->clinicId,
                'code' => 'FORGED-LOCATION',
                'name' => 'Ubicación manipulada',
                'type' => 'storage',
            ])
            ->assertRedirect(route('inventory.locations.index'))
            ->assertSessionHasErrors('clinic_id');

        $this->postJson(route('inventory.transfer'), [
            'inventory_id' => $fixture['inventory_a']->id,
            'destination_inventory_id' => $fixture['inventory_b']->id,
            'quantity' => 1,
            'reason' => 'Intento entre clínicas',
        ])->assertUnprocessable()->assertJsonValidationErrors('destination_inventory_id');

        $invoiceCount = Invoice::count();
        $this->from(route('billing.create'))
            ->post(route('billing.store'), [
                'clinic_id' => $fixture['context_b']->clinicId,
                'patient_id' => $fixture['patient_b']->id,
                'staff_id' => $fixture['staff_a']->id,
                'invoice_date' => '2026-09-03',
                'items' => [[
                    'cdt_catalog_id' => $fixture['catalog']->id,
                    'quantity' => 1,
                    'unit_price' => 25,
                ]],
            ])
            ->assertRedirect(route('billing.create'))
            ->assertSessionHasErrors(['clinic_id', 'patient_id']);

        $this->assertSame($invoiceCount, Invoice::count());
        $this->assertDatabaseMissing('inventory_locations', ['code' => 'FORGED-LOCATION']);

        $paymentCount = Payment::query()->count();
        $this->postJson(route('payments.store'), [
            'invoice_id' => $fixture['invoice_b']->id,
            'amount' => 1,
            'payment_method' => 'cash',
            'payment_date' => '2026-09-03',
        ])->assertUnprocessable()->assertJsonValidationErrors('invoice_id');
        $this->assertSame($paymentCount, Payment::query()->count());
    }

    public function test_payment_cannot_exceed_the_scoped_invoice_balance(): void
    {
        $fixture = $this->fixture();
        $this->actingAs($fixture['user'])->withSession(['clinic_id' => $fixture['context_a']->clinicId]);
        $paymentCount = Payment::query()->count();

        $this->from(route('payments.create'))->post(route('payments.store'), [
            'invoice_id' => $fixture['invoice_a']->id,
            'amount' => 90,
            'payment_method' => 'cash',
            'payment_date' => '2026-09-03',
        ])->assertRedirect(route('payments.create'))->assertSessionHasErrors('amount');

        $this->assertSame($paymentCount, Payment::query()->count());
    }

    public function test_cross_clinic_updates_and_deletions_are_hidden_as_not_found(): void
    {
        $fixture = $this->fixture();
        $this->actingAs($fixture['user'])->withSession(['clinic_id' => $fixture['context_a']->clinicId]);

        $this->put(route('inventory.update', $fixture['inventory_b']), [
            'current_stock' => 0,
            'reserved_stock' => 0,
            'average_cost' => 1,
        ])->assertNotFound();

        $this->put(route('billing.update', $fixture['invoice_b']), [
            'invoice_date' => '2026-09-03',
            'items' => [],
        ])->assertNotFound();
        $this->delete(route('billing.destroy', $fixture['invoice_b']), [
            'reason' => 'Intento entre clínicas',
        ])->assertNotFound();

        $this->put(route('payments.update', $fixture['payment_b']), [
            'invoice_id' => $fixture['invoice_b']->id,
            'amount' => 1,
            'payment_method' => 'cash',
            'payment_date' => '2026-09-03',
        ])->assertNotFound();
        $this->delete(route('payments.destroy', $fixture['payment_b']))->assertNotFound();

        $this->assertDatabaseHas('invoices', ['id' => $fixture['invoice_b']->id]);
        $this->assertDatabaseHas('payments', ['id' => $fixture['payment_b']->id]);
    }

    public function test_clinic_permissions_are_required_even_when_a_global_role_could_exist(): void
    {
        $fixture = $this->fixture();
        $unauthorized = User::factory()->create(['is_active' => true]);
        $unauthorizedContext = $this->clinicalContextFor($unauthorized, []);
        $globalAdmin = Role::query()->create([
            'name' => 'admin',
            'display_name' => 'Administrador global heredado',
            'permissions' => ['view_inventory', 'view_billing'],
            'is_active' => true,
        ]);
        $unauthorized->roles()->attach($globalAdmin);

        $this->assertTrue($unauthorized->fresh()->hasPermission('view_inventory'));
        $this->assertTrue($unauthorized->fresh()->hasPermission('view_billing'));

        $this->actingAs($unauthorized)->withSession(['clinic_id' => $unauthorizedContext->clinicId])
            ->get(route('inventory.index'))
            ->assertForbidden();

        $this->get(route('billing.index'))->assertForbidden();
    }

    public function test_inventory_cache_values_and_keys_do_not_cross_clinics(): void
    {
        $fixture = $this->fixture();
        $cache = app(CacheService::class);

        $clinicA = $cache->getInventoryStatistics($fixture['context_a']);
        $clinicB = $cache->getInventoryStatistics($fixture['context_b']);

        $this->assertSame(1, $clinicA['total_products']);
        $this->assertSame(1, $clinicB['total_products']);
        $this->assertSame(0, $clinicA['out_of_stock_count']);
        $this->assertSame(1, $clinicB['out_of_stock_count']);
    }

    /**
     * @return array<string, mixed>
     */
    private function fixture(): array
    {
        $permissions = [
            'view_inventory',
            'manage_inventory',
            'adjust_inventory',
            'export_inventory',
            'view_billing',
            'manage_billing',
            'view_payments',
            'manage_payments',
        ];
        $user = User::factory()->create(['is_active' => true]);
        $contextA = $this->clinicalContextFor($user, $permissions);
        $contextB = $this->clinicalContextFor($user, $permissions);
        [$inventoryA, $locationA] = $this->inventory($user, $contextA, 'A', 5);
        [$inventoryB, $locationB] = $this->inventory($user, $contextB, 'B', 0);
        [$patientA, $staffA] = $this->clinicalPeople($user, $contextA, 'A');
        [$patientB, $staffB] = $this->clinicalPeople($user, $contextB, 'B');
        $catalog = CdtCatalog::query()->create([
            'cdt_code' => 'ISO-'.uniqid(),
            'category' => 'Preventivo',
            'procedure_name' => 'Procedimiento aislado',
            'base_price' => 25,
            'is_active' => true,
        ]);
        $invoiceA = $this->invoice($user, $contextA, $patientA, $staffA, 'A');
        $invoiceB = $this->invoice($user, $contextB, $patientB, $staffB, 'B');
        $paymentA = $this->payment($user, $contextA, $patientA, $invoiceA, 'A', 11.11);
        $paymentB = $this->payment($user, $contextB, $patientB, $invoiceB, 'B', 99.99);

        return compact(
            'user',
            'contextA',
            'contextB',
            'inventoryA',
            'inventoryB',
            'locationA',
            'locationB',
            'patientA',
            'patientB',
            'staffA',
            'staffB',
            'catalog',
            'invoiceA',
            'invoiceB',
            'paymentA',
            'paymentB',
        ) + [
            'context_a' => $contextA,
            'context_b' => $contextB,
            'inventory_a' => $inventoryA,
            'inventory_b' => $inventoryB,
            'patient_a' => $patientA,
            'patient_b' => $patientB,
            'staff_a' => $staffA,
            'staff_b' => $staffB,
            'invoice_a' => $invoiceA,
            'invoice_b' => $invoiceB,
            'payment_a' => $paymentA,
            'payment_b' => $paymentB,
        ];
    }

    /** @return array{Inventory, InventoryLocation} */
    private function inventory(User $user, ClinicContext $context, string $suffix, int $stock): array
    {
        $location = new InventoryLocation([
            'code' => 'ISO-LOC-'.$suffix.'-'.uniqid(),
            'name' => 'Ubicación clínica '.$suffix,
            'type' => 'clinic',
            'is_active' => true,
        ]);
        $location->forceFill(['clinic_id' => $context->clinicId])->save();

        $product = Product::query()->create([
            'product_code' => 'ISO-PROD-'.$suffix.'-'.uniqid(),
            'name' => 'Material clínica '.$suffix,
            'category' => 'materiales',
            'unit_of_measure' => 'piezas',
            'minimum_stock' => 1,
            'created_by' => $user->id,
            'is_active' => true,
        ]);

        $inventory = new Inventory([
            'product_id' => $product->id,
            'inventory_location_id' => $location->id,
            'current_stock' => $stock,
            'available_stock' => $stock,
            'average_cost' => 2,
            'location' => $location->name,
        ]);
        $inventory->forceFill(['clinic_id' => $context->clinicId])->save();

        return [$inventory, $location];
    }

    /** @return array{Patient, Staff} */
    private function clinicalPeople(User $user, ClinicContext $context, string $suffix): array
    {
        $patient = new Patient([
            'patient_code' => 'ISO-PAT-'.$suffix.'-'.uniqid(),
            'first_name' => 'Paciente',
            'last_name' => 'Clínica '.$suffix,
            'birth_date' => '1990-01-01',
            'gender' => 'female',
            'is_active' => true,
            'created_by' => $user->id,
        ]);
        $patient->forceFill(['clinic_id' => $context->clinicId])->save();

        $staff = new Staff([
            'user_id' => $user->id,
            'employee_id' => 'ISO-STAFF-'.$suffix.'-'.uniqid(),
            'experience_years' => 0,
            'is_available' => true,
            'is_active' => true,
        ]);
        $staff->forceFill(['clinic_id' => $context->clinicId])->save();

        return [$patient, $staff];
    }

    private function invoice(
        User $user,
        ClinicContext $context,
        Patient $patient,
        Staff $staff,
        string $suffix,
    ): Invoice {
        $invoice = new Invoice([
            'invoice_number' => 'INV-ISO-'.$suffix.'-'.uniqid(),
            'patient_id' => $patient->id,
            'staff_id' => $staff->id,
            'invoice_date' => '2026-09-03',
            'status' => 'draft',
            'total_amount' => 100,
            'balance_due' => 100,
            'created_by' => $user->id,
        ]);
        $invoice->forceFill(['clinic_id' => $context->clinicId])->save();

        return $invoice;
    }

    private function payment(
        User $user,
        ClinicContext $context,
        Patient $patient,
        Invoice $invoice,
        string $suffix,
        float $amount,
    ): Payment {
        $payment = new Payment([
            'payment_number' => 'PAY-ISO-'.$suffix.'-'.uniqid(),
            'invoice_id' => $invoice->id,
            'patient_id' => $patient->id,
            'payment_date' => '2026-09-03',
            'amount' => $amount,
            'payment_method' => 'cash',
            'status' => 'completed',
            'processed_by' => $user->id,
        ]);
        $payment->forceFill(['clinic_id' => $context->clinicId])->save();

        return $payment;
    }
}
