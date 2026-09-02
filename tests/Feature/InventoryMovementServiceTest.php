<?php

namespace Tests\Feature;

use App\Data\InventoryMovementData;
use App\Models\Inventory;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\User;
use App\Services\InventoryMovementService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use RuntimeException;
use Tests\TestCase;

class InventoryMovementServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_restock_updates_inventory_and_creates_auditable_movement(): void
    {
        [$user, $inventory] = $this->inventoryFixture(5);

        $movement = app(InventoryMovementService::class)->adjust(
            new InventoryMovementData($inventory->id, $inventory->product_id, 'restock', 3, 'Compra recibida'),
            $user
        );

        $this->assertSame(8, $inventory->fresh()->current_stock);
        $this->assertSame(8, $inventory->fresh()->available_stock);
        $this->assertSame(5, $movement->stock_before);
        $this->assertSame(8, $movement->stock_after);
        $this->assertSame($user->id, $movement->user_id);
        $this->assertSame('Compra recibida', $movement->reason);
        $this->assertDatabaseHas('activity_log', [
            'subject_type' => InventoryMovement::class,
            'subject_id' => $inventory->id,
            'causer_id' => $user->id,
            'log_name' => 'inventory',
            'description' => 'inventory.movement.created',
        ]);
    }

    public function test_adjustment_rejects_invalid_input_before_database_work(): void
    {
        [$user, $inventory] = $this->inventoryFixture(5);

        $this->expectException(InvalidArgumentException::class);

        app(InventoryMovementService::class)->adjust(
            new InventoryMovementData($inventory->id, $inventory->product_id, 'invalid', 0),
            $user
        );
    }

    public function test_consumption_rejects_negative_result(): void
    {
        [$user, $inventory] = $this->inventoryFixture(2);

        $this->expectException(RuntimeException::class);

        app(InventoryMovementService::class)->adjust(
            new InventoryMovementData($inventory->id, $inventory->product_id, 'consumption', 3, 'Uso clínico'),
            $user
        );

        $this->assertSame(2, $inventory->fresh()->current_stock);
        $this->assertDatabaseCount('inventory_movements', 0);
    }

    private function inventoryFixture(int $stock): array
    {
        $user = User::factory()->create();
        $product = Product::query()->create([
            'product_code' => 'TEST-' . uniqid(),
            'name' => 'Producto de prueba',
            'category' => 'materiales',
            'unit_of_measure' => 'piezas',
            'minimum_stock' => 1,
            'created_by' => $user->id,
        ]);
        $inventory = Inventory::query()->create([
            'product_id' => $product->id,
            'current_stock' => $stock,
            'available_stock' => $stock,
        ]);

        return [$user, $inventory];
    }
}
