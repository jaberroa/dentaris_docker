<?php

namespace Tests\Unit;

use App\Models\InventoryMovement;
use Tests\TestCase;

class InventoryMovementStructureTest extends TestCase
{
    public function test_inventory_movement_exposes_expected_attributes_and_relations(): void
    {
        $movement = new InventoryMovement();

        $this->assertContains('inventory_id', $movement->getFillable());
        $this->assertContains('product_id', $movement->getFillable());
        $this->assertContains('type', $movement->getFillable());
        $this->assertContains('quantity', $movement->getFillable());
        $this->assertContains('stock_before', $movement->getFillable());
        $this->assertContains('stock_after', $movement->getFillable());
        $this->assertContains('reason', $movement->getFillable());
        $this->assertArrayHasKey('metadata', $movement->getCasts());
        $this->assertTrue(method_exists($movement, 'inventory'));
        $this->assertTrue(method_exists($movement, 'product'));
        $this->assertTrue(method_exists($movement, 'user'));
        $this->assertTrue(method_exists($movement, 'reference'));
    }
}
