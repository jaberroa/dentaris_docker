<?php

namespace Tests\Unit;

use App\Models\Invoice;
use App\Models\Inventory;
use App\Models\InventoryMovement;
use App\Models\User;
use App\Modules\Clinics\Data\ClinicContext;
use App\Policies\InvoicePolicy;
use App\Policies\InventoryMovementPolicy;
use App\Policies\InventoryPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithClinicalContext;
use Tests\TestCase;

class DomainPolicyTest extends TestCase
{
    use RefreshDatabase;
    use InteractsWithClinicalContext;

    public function test_invoice_policy_requires_billing_permission_and_rejects_paid_invoice_updates(): void
    {
        [$user, $context] = $this->userWithPermissions(['manage_billing', 'view_billing']);
        $invoice = (new Invoice())->forceFill(['status' => 'draft', 'clinic_id' => $context->clinicId]);
        $paidInvoice = (new Invoice())->forceFill(['status' => 'paid', 'clinic_id' => $context->clinicId]);
        $policy = app(InvoicePolicy::class);
        $this->bindClinicalContext($context, $user);

        $this->assertTrue($policy->view($user, $invoice)->allowed());
        $this->assertTrue($policy->update($user, $invoice)->allowed());
        $this->assertTrue($policy->update($user, $paidInvoice)->denied());

        [$unauthorized, $unauthorizedContext] = $this->userWithPermissions([], $context->clinicId);
        $this->bindClinicalContext($unauthorizedContext, $unauthorized);
        $this->assertTrue($policy->view($unauthorized, $invoice)->denied());
    }

    public function test_inventory_policy_separates_management_adjustment_and_export_permissions(): void
    {
        $policy = app(InventoryPolicy::class);

        [$manager, $managerContext] = $this->userWithPermissions(['manage_inventory']);
        $inventory = (new Inventory())->forceFill(['clinic_id' => $managerContext->clinicId]);
        $this->bindClinicalContext($managerContext, $manager);
        $this->assertTrue($policy->update($manager, $inventory)->allowed());
        $this->assertTrue($policy->adjust($manager, $inventory)->denied());

        [$adjuster, $adjusterContext] = $this->userWithPermissions(['adjust_inventory']);
        $adjusterInventory = (new Inventory())->forceFill(['clinic_id' => $adjusterContext->clinicId]);
        $this->bindClinicalContext($adjusterContext, $adjuster);
        $this->assertTrue($policy->adjust($adjuster, $adjusterInventory)->allowed());

        [$exporter, $exporterContext] = $this->userWithPermissions(['export_inventory']);
        $this->bindClinicalContext($exporterContext, $exporter);
        $this->assertTrue($policy->export($exporter));
        $this->bindClinicalContext($managerContext, $manager);
        $this->assertFalse($policy->export($manager));
    }

    public function test_inventory_movement_reversal_requires_adjustment_permission(): void
    {
        $policy = app(InventoryMovementPolicy::class);
        [$adjuster, $context] = $this->userWithPermissions(['adjust_inventory']);
        $movement = (new InventoryMovement())->forceFill(['clinic_id' => $context->clinicId]);
        $this->bindClinicalContext($context, $adjuster);
        $this->assertTrue($policy->reverse($adjuster, $movement)->allowed());

        [$manager, $managerContext] = $this->userWithPermissions(['manage_inventory']);
        $managerMovement = (new InventoryMovement())->forceFill(['clinic_id' => $managerContext->clinicId]);
        $this->bindClinicalContext($managerContext, $manager);
        $this->assertTrue($policy->reverse($manager, $managerMovement)->denied());
    }

    public function test_inventory_transfer_movements_cannot_be_reverted_individually(): void
    {
        $policy = app(InventoryMovementPolicy::class);
        [$adjuster, $context] = $this->userWithPermissions(['adjust_inventory']);
        $movement = (new InventoryMovement(['metadata' => ['transfer_id' => 'transfer-test']]))
            ->forceFill(['clinic_id' => $context->clinicId]);
        $this->bindClinicalContext($context, $adjuster);

        $this->assertTrue($policy->reverse($adjuster, $movement)->denied());
    }

    /** @return array{User, ClinicContext} */
    private function userWithPermissions(array $permissions, ?int $clinicId = null): array
    {
        $user = User::factory()->create(['is_active' => true]);
        $context = $this->clinicalContextFor($user, $permissions, $clinicId);

        return [$user, $context];
    }
}
