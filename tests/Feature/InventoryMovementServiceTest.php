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
use Tests\Concerns\InteractsWithClinicalContext;
use Tests\TestCase;

class InventoryMovementServiceTest extends TestCase
{
    use RefreshDatabase;
    use InteractsWithClinicalContext;

    public function test_restock_updates_inventory_and_creates_auditable_movement(): void
    {
        [$user, $inventory, $context] = $this->inventoryFixture(5);

        $movement = app(InventoryMovementService::class)->adjust(
            new InventoryMovementData($inventory->id, $inventory->product_id, 'restock', 3, 'Compra recibida'),
            $user,
            $context,
        );

        $this->assertSame(8, $inventory->fresh()->current_stock);
        $this->assertSame(8, $inventory->fresh()->available_stock);
        $this->assertSame(5, $movement->stock_before);
        $this->assertSame(8, $movement->stock_after);
        $this->assertSame($user->id, $movement->user_id);
        $this->assertSame('Compra recibida', $movement->reason);
        $this->assertDatabaseHas('activity_log', [
            'subject_type' => InventoryMovement::class,
            'subject_id' => $movement->id,
            'causer_id' => $user->id,
            'log_name' => 'inventory',
            'description' => 'inventory.movement.created',
        ]);
    }

    public function test_adjustment_rejects_invalid_input_before_database_work(): void
    {
        [$user, $inventory, $context] = $this->inventoryFixture(5);

        $this->expectException(InvalidArgumentException::class);

        app(InventoryMovementService::class)->adjust(
            new InventoryMovementData($inventory->id, $inventory->product_id, 'invalid', 0),
            $user,
            $context,
        );
    }

    public function test_consumption_rejects_negative_result(): void
    {
        [$user, $inventory, $context] = $this->inventoryFixture(2);

        $this->expectException(RuntimeException::class);

        app(InventoryMovementService::class)->adjust(
            new InventoryMovementData($inventory->id, $inventory->product_id, 'consumption', 3, 'Uso clínico'),
            $user,
            $context,
        );

        $this->assertSame(2, $inventory->fresh()->current_stock);
        $this->assertDatabaseCount('inventory_movements', 0);
    }

    public function test_reversal_creates_compensating_movement_without_deleting_original(): void
    {
        [$user, $inventory, $context] = $this->inventoryFixture(5);
        $service = app(InventoryMovementService::class);

        $original = $service->adjust(
            new InventoryMovementData($inventory->id, $inventory->product_id, 'restock', 3, 'Compra recibida'),
            $user,
            $context,
        );
        $reversal = $service->reverse($original, $user, $context);

        $this->assertSame(5, $inventory->fresh()->current_stock);
        $this->assertSame('consumption', $reversal->type);
        $this->assertSame(3, $reversal->quantity);
        $this->assertSame(InventoryMovement::class, $reversal->reference_type);
        $this->assertSame($original->id, $reversal->reference_id);
        $this->assertDatabaseCount('inventory_movements', 2);

        $this->expectException(InvalidArgumentException::class);
        $service->reverse($original, $user, $context);
    }

    private function inventoryFixture(int $stock): array
    {
        $user = User::factory()->create(['is_active' => true]);
        $context = $this->clinicalContextFor($user, ['adjust_inventory']);
        $product = Product::query()->create([
            'product_code' => 'TEST-' . uniqid(),
            'name' => 'Producto de prueba',
            'category' => 'materiales',
            'unit_of_measure' => 'piezas',
            'minimum_stock' => 1,
            'created_by' => $user->id,
        ]);
        $inventory = new Inventory([
            'product_id' => $product->id,
            'current_stock' => $stock,
            'available_stock' => $stock,
        ]);
        $inventory->forceFill(['clinic_id' => $context->clinicId])->save();

        return [$user, $inventory, $context];
    }
}
