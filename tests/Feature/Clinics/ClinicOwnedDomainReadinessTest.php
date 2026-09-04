<?php

namespace Tests\Feature\Clinics;

use App\Models\Inventory;
use App\Models\Invoice;
use App\Models\Patient;
use App\Models\Product;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\InteractsWithClinicalContext;
use Tests\TestCase;

class ClinicOwnedDomainReadinessTest extends TestCase
{
    use InteractsWithClinicalContext;
    use RefreshDatabase;

    public function test_inventory_fails_closed_while_a_legacy_row_has_no_clinic_owner(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $context = $this->clinicalContextFor($user, ['view_inventory']);
        $product = Product::query()->create([
            'product_code' => 'LEGACY-'.uniqid(),
            'name' => 'Inventario heredado sin propietario',
            'category' => 'materiales',
            'unit_of_measure' => 'piezas',
            'minimum_stock' => 1,
            'created_by' => $user->id,
        ]);

        Inventory::query()->create([
            'product_id' => $product->id,
            'current_stock' => 1,
            'available_stock' => 1,
        ]);

        $this->actingAs($user)->withSession(['clinic_id' => $context->clinicId])
            ->getJson(route('inventory.index'))
            ->assertStatus(503)
            ->assertJsonPath('code', 'CLINIC_DOMAIN_NOT_READY');

        $this->get(route('inventory.index'))
            ->assertStatus(503)
            ->assertSee('Módulo en preparación clínica');
    }

    public function test_dashboard_remains_clinic_scoped_and_omits_an_unready_domain(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $context = $this->clinicalContextFor($user, []);
        $clinicName = DB::table('clinics')->where('id', $context->clinicId)->value('name');
        $product = Product::query()->create([
            'product_code' => 'DASH-LEGACY-'.uniqid(),
            'name' => 'Dato heredado no visible',
            'category' => 'materiales',
            'unit_of_measure' => 'piezas',
            'minimum_stock' => 1,
            'created_by' => $user->id,
        ]);
        Inventory::query()->create([
            'product_id' => $product->id,
            'current_stock' => 100,
            'available_stock' => 100,
        ]);

        $this->actingAs($user)->withSession(['clinic_id' => $context->clinicId])
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee($clinicName)
            ->assertDontSee('Dato heredado no visible');
    }

    public function test_billing_fails_closed_while_a_legacy_invoice_has_no_clinic_owner(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $context = $this->clinicalContextFor($user, ['view_billing']);
        $patient = new Patient([
            'patient_code' => 'LEGACY-PAT-'.uniqid(),
            'first_name' => 'Paciente',
            'last_name' => 'Heredado',
            'birth_date' => '1990-01-01',
            'gender' => 'female',
            'is_active' => true,
            'created_by' => $user->id,
        ]);
        $patient->forceFill(['clinic_id' => $context->clinicId])->save();
        $staff = new Staff([
            'user_id' => $user->id,
            'employee_id' => 'LEGACY-STAFF-'.uniqid(),
            'is_active' => true,
        ]);
        $staff->forceFill(['clinic_id' => $context->clinicId])->save();
        Invoice::query()->create([
            'invoice_number' => 'LEGACY-INV-'.uniqid(),
            'patient_id' => $patient->id,
            'staff_id' => $staff->id,
            'invoice_date' => '2026-09-03',
            'status' => 'draft',
            'created_by' => $user->id,
        ]);

        $this->actingAs($user)->withSession(['clinic_id' => $context->clinicId])
            ->getJson(route('billing.index'))
            ->assertStatus(503)
            ->assertJsonPath('code', 'CLINIC_DOMAIN_NOT_READY');
    }
}
