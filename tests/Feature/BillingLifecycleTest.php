<?php

namespace Tests\Feature;

use App\Models\CdtCatalog;
use App\Models\Invoice;
use App\Models\Patient;
use App\Models\Role;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BillingLifecycleTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_with_billing_management_permission_sees_the_create_invoice_action(): void
    {
        [$user] = $this->fixture();

        $this->actingAs($user)
            ->get(route('billing.index'))
            ->assertOk()
            ->assertSee('Nueva factura');
    }

    public function test_authorized_user_can_edit_only_a_draft_invoice_without_payments(): void
    {
        [$user, $invoice, $catalogItem] = $this->fixture();

        $this->actingAs($user)
            ->put(route('billing.update', $invoice), [
                'invoice_date' => '2026-09-02',
                'due_date' => '2026-09-12',
                'tax_rate' => 16,
                'discount_amount' => 2,
                'notes' => 'Factura actualizada',
                'items' => [[
                    'cdt_catalog_id' => $catalogItem->id,
                    'quantity' => 2,
                    'unit_price' => 25,
                ]],
            ])
            ->assertRedirect(route('billing.show', $invoice));

        $this->assertDatabaseHas('invoices', [
            'id' => $invoice->id,
            'status' => 'draft',
            'subtotal' => 50,
            'tax_amount' => 8,
            'total_amount' => 56,
            'balance_due' => 56,
        ]);
        $this->assertDatabaseHas('invoice_items', [
            'invoice_id' => $invoice->id,
            'item_name' => 'Profilaxis dental',
            'quantity' => 2,
            'total_price' => 50,
        ]);
        $this->assertDatabaseHas('activity_log', [
            'subject_type' => Invoice::class,
            'subject_id' => $invoice->id,
            'description' => 'invoice.updated',
        ]);
    }

    public function test_send_and_cancellation_change_state_without_deleting_the_invoice(): void
    {
        [$user, $invoice] = $this->fixture();

        $this->actingAs($user)
            ->post(route('billing.send', $invoice))
            ->assertSessionHas('success');
        $this->assertSame('sent', $invoice->fresh()->status);

        $this->actingAs($user)
            ->delete(route('billing.destroy', $invoice), ['reason' => 'Paciente canceló el tratamiento'])
            ->assertRedirect(route('billing.index'));

        $this->assertDatabaseHas('invoices', ['id' => $invoice->id, 'status' => 'cancelled']);
        $this->assertDatabaseHas('activity_log', [
            'subject_type' => Invoice::class,
            'subject_id' => $invoice->id,
            'description' => 'invoice.cancelled',
        ]);
    }

    private function fixture(): array
    {
        $user = User::factory()->create();
        $role = Role::query()->create([
            'name' => 'billing-'.uniqid(),
            'display_name' => 'Facturación de prueba',
            'permissions' => ['manage_billing', 'view_billing'],
        ]);
        $user->roles()->attach($role);

        $patient = Patient::query()->create([
            'patient_code' => 'PAC-'.uniqid(),
            'first_name' => 'Ana',
            'last_name' => 'Prueba',
            'birth_date' => '1990-01-01',
            'gender' => 'female',
            'created_by' => $user->id,
        ]);
        $staff = Staff::query()->create([
            'user_id' => $user->id,
            'employee_id' => 'EMP-'.uniqid(),
            'is_active' => true,
        ]);
        $catalogItem = CdtCatalog::query()->create([
            'cdt_code' => 'D1110-'.uniqid(),
            'category' => 'Preventivo',
            'procedure_name' => 'Profilaxis dental',
            'base_price' => 25,
        ]);
        $invoice = Invoice::query()->create([
            'invoice_number' => 'INV-'.uniqid(),
            'patient_id' => $patient->id,
            'staff_id' => $staff->id,
            'invoice_date' => '2026-09-01',
            'due_date' => '2026-09-10',
            'status' => 'draft',
            'created_by' => $user->id,
        ]);
        $invoice->items()->create([
            'cdt_catalog_id' => $catalogItem->id,
            'item_name' => $catalogItem->procedure_name,
            'quantity' => 1,
            'unit_price' => 25,
            'total_price' => 25,
        ]);
        $invoice->calculateTotals();

        return [$user, $invoice, $catalogItem];
    }
}
