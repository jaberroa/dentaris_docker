<?php

namespace Tests\Feature;

use App\Models\Inventory;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithClinicalContext;
use Tests\TestCase;

class InventoryShowTest extends TestCase
{
    use RefreshDatabase;
    use InteractsWithClinicalContext;

    public function test_inventory_detail_renders_movement_history(): void
    {
        [$user, $inventory, $context] = $this->fixture();

        $movement = $inventory->movements()->make([
            'product_id' => $inventory->product_id,
            'user_id' => $user->id,
            'type' => 'restock',
            'quantity' => 4,
            'stock_before' => 0,
            'stock_after' => 4,
            'reason' => 'Recepción de compra',
        ]);
        $movement->forceFill(['clinic_id' => $context->clinicId])->save();

        $this->actingAs($user)->withSession(['clinic_id' => $context->clinicId])
            ->get(route('inventory.show', $inventory))
            ->assertOk()
            ->assertSee('Historial de movimientos')
            ->assertSee('Recepción de compra')
            ->assertSee('Entrada');
    }

    private function fixture(): array
    {
        $user = User::factory()->create(['is_active' => true]);
        $context = $this->clinicalContextFor($user, ['view_inventory']);

        $product = Product::query()->create([
            'product_code' => 'SHOW-' . uniqid(),
            'name' => 'Producto de historial',
            'category' => 'materiales',
            'unit_of_measure' => 'piezas',
            'minimum_stock' => 1,
            'created_by' => $user->id,
        ]);
        $inventory = new Inventory([
            'product_id' => $product->id,
            'current_stock' => 4,
            'available_stock' => 4,
        ]);
        $inventory->forceFill(['clinic_id' => $context->clinicId])->save();

        return [$user, $inventory, $context];
    }
}
