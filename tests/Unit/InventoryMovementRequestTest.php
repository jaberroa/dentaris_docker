<?php

namespace Tests\Unit;

use App\Http\Requests\Inventory\CreateInventoryAdjustmentRequest;
use App\Http\Requests\Inventory\ExportInventoryRequest;
use App\Http\Requests\Inventory\TransferInventoryRequest;
use Tests\TestCase;

class InventoryMovementRequestTest extends TestCase
{
    public function test_inventory_requests_define_authorization_and_validation_rules(): void
    {
        foreach ([
            CreateInventoryAdjustmentRequest::class,
            TransferInventoryRequest::class,
            ExportInventoryRequest::class,
        ] as $requestClass) {
            $request = new $requestClass();

            $this->assertTrue(method_exists($request, 'authorize'));
            $this->assertTrue(method_exists($request, 'rules'));
            $this->assertNotEmpty($request->rules());
        }
    }

    public function test_transfer_request_requires_different_inventories(): void
    {
        $rules = (new TransferInventoryRequest())->rules();

        $this->assertContains('different:inventory_id', $rules['destination_inventory_id']);
        $this->assertContains('min:1', $rules['quantity']);
    }
}
