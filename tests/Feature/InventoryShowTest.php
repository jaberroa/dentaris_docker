<?php

namespace Tests\Feature;

use App\Models\Inventory;
use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryShowTest extends TestCase
{
    use RefreshDatabase;

    public function test_inventory_detail_renders_movement_history(): void
    {
        [$user, $inventory] = $this->fixture();

        $inventory->movements()->create([
            'product_id' => $inventory->product_id,
            'user_id' => $user->id,
            'type' => 'restock',
            'quantity' => 4,
            'stock_before' => 0,
            'stock_after' => 4,
            'reason' => 'Recepción de compra',
        ]);

        $this->actingAs($user)
            ->get(route('inventory.show', $inventory))
            ->assertOk()
            ->assertSee('Historial de movimientos')
            ->assertSee('Recepción de compra')
            ->assertSee('Entrada');
    }

    private function fixture(): array
    {
        $user = User::factory()->create();
        $role = Role::query()->create([
            'name' => 'inventory-viewer-' . uniqid(),
            'display_name' => 'Visor de inventario',
            'permissions' => ['view_inventory'],
        ]);
        $user->roles()->attach($role);

        $product = Product::query()->create([
            'product_code' => 'SHOW-' . uniqid(),
            'name' => 'Producto de historial',
            'category' => 'materiales',
            'unit_of_measure' => 'piezas',
            'minimum_stock' => 1,
            'created_by' => $user->id,
        ]);
        $inventory = Inventory::query()->create([
            'product_id' => $product->id,
            'current_stock' => 4,
            'available_stock' => 4,
        ]);

        return [$user, $inventory];
    }
}
