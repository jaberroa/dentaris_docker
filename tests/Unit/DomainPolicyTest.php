<?php

namespace Tests\Unit;

use App\Models\Invoice;
use App\Models\Inventory;
use App\Models\User;
use App\Policies\InvoicePolicy;
use App\Policies\InventoryPolicy;
use Tests\TestCase;

class DomainPolicyTest extends TestCase
{
    public function test_invoice_policy_requires_billing_permission_and_rejects_paid_invoice_updates(): void
    {
        $user = $this->userWithPermissions(['manage_billing', 'view_billing']);
        $invoice = (new Invoice())->forceFill(['status' => 'draft']);
        $paidInvoice = (new Invoice())->forceFill(['status' => 'paid']);
        $policy = new InvoicePolicy();

        $this->assertTrue($policy->view($user, $invoice));
        $this->assertTrue($policy->update($user, $invoice));
        $this->assertFalse($policy->update($user, $paidInvoice));
        $this->assertFalse($policy->view($this->userWithPermissions([]), $invoice));
    }

    public function test_inventory_policy_separates_management_adjustment_and_export_permissions(): void
    {
        $inventory = new Inventory();
        $policy = new InventoryPolicy();

        $manager = $this->userWithPermissions(['manage_inventory']);
        $adjuster = $this->userWithPermissions(['adjust_inventory']);
        $exporter = $this->userWithPermissions(['export_inventory']);

        $this->assertTrue($policy->update($manager, $inventory));
        $this->assertFalse($policy->adjust($manager, $inventory));
        $this->assertTrue($policy->adjust($adjuster, $inventory));
        $this->assertTrue($policy->export($exporter));
        $this->assertFalse($policy->export($manager));
    }

    private function userWithPermissions(array $permissions): User
    {
        $user = new User();
        $role = new \App\Models\Role();
        $role->forceFill(['name' => 'test', 'permissions' => $permissions]);

        return $user->setRelation('roles', new \Illuminate\Database\Eloquent\Collection([$role]));
    }
}
