<?php

namespace Tests\Feature;

use App\Models\Inventory;
use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryAdjustmentHttpTest extends TestCase
{
    use RefreshDatabase;

    public function test_authorized_user_can_adjust_inventory_over_http(): void
    {
        [$user, $inventory] = $this->fixture(['manage_inventory', 'adjust_inventory']);

        $response = $this->actingAs($user)->postJson(
            route('inventory.adjust', $inventory),
            [
                'inventory_id' => $inventory->id,
                'product_id' => $inventory->product_id,
                'type' => 'restock',
                'quantity' => 4,
                'reason' => 'Recepción de compra',
            ]
        );

        $response->assertCreated()->assertJsonPath('data.type', 'restock');
        $this->assertSame(4, $inventory->fresh()->current_stock);
        $this->assertDatabaseHas('inventory_movements', [
            'inventory_id' => $inventory->id,
            'quantity' => 4,
            'reason' => 'Recepción de compra',
        ]);
    }

    public function test_user_without_adjust_permission_is_rejected(): void
    {
        [$user, $inventory] = $this->fixture(['manage_inventory']);

        $this->actingAs($user)
            ->postJson(route('inventory.adjust', $inventory), [
                'inventory_id' => $inventory->id,
                'product_id' => $inventory->product_id,
                'type' => 'restock',
                'quantity' => 4,
                'reason' => 'Intento no autorizado',
            ])
            ->assertForbidden();

        $this->assertSame(0, $inventory->fresh()->current_stock);
        $this->assertDatabaseCount('inventory_movements', 0);
    }

    public function test_invalid_adjustment_is_rejected_before_persistence(): void
    {
        [$user, $inventory] = $this->fixture(['manage_inventory', 'adjust_inventory']);

        $this->actingAs($user)
            ->postJson(route('inventory.adjust', $inventory), [
                'inventory_id' => $inventory->id,
                'product_id' => $inventory->product_id,
                'type' => 'restock',
                'quantity' => 0,
                'reason' => '',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['quantity', 'reason']);

        $this->assertDatabaseCount('inventory_movements', 0);
    }

    private function fixture(array $permissions): array
    {
        $user = User::factory()->create();
        $role = Role::query()->create([
            'name' => 'test-' . uniqid(),
            'display_name' => 'Rol de prueba',
            'permissions' => $permissions,
        ]);
        $user->roles()->attach($role);

        $product = Product::query()->create([
            'product_code' => 'HTTP-' . uniqid(),
            'name' => 'Producto HTTP',
            'category' => 'materiales',
            'unit_of_measure' => 'piezas',
            'minimum_stock' => 1,
            'created_by' => $user->id,
        ]);
        $inventory = Inventory::query()->create([
            'product_id' => $product->id,
            'current_stock' => 0,
            'available_stock' => 0,
        ]);

        return [$user, $inventory];
    }
}
