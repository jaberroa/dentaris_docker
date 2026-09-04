<?php

namespace Tests\Feature;

use App\Models\Inventory;
use App\Models\InventoryLocation;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithClinicalContext;
use Tests\TestCase;

class InventoryLocationManagementTest extends TestCase
{
    use RefreshDatabase;
    use InteractsWithClinicalContext;

    public function test_inventory_manager_can_create_a_location(): void
    {
        [$user, $context] = $this->manager();

        $this->actingAs($user)->withSession(['clinic_id' => $context->clinicId])
            ->post(route('inventory.locations.store'), [
                'code' => 'CONS-01',
                'name' => 'Consultorio 1',
                'type' => 'clinic',
                'notes' => 'Ubicación de prueba',
            ])
            ->assertRedirect(route('inventory.locations.index'));

        $this->assertDatabaseHas('inventory_locations', [
            'code' => 'CONS-01',
            'name' => 'Consultorio 1',
            'is_active' => true,
            'clinic_id' => $context->clinicId,
        ]);
    }

    public function test_inventory_manager_can_create_an_empty_stock_location_for_a_product(): void
    {
        [$user, $context] = $this->manager();
        $source = $this->inventory($user, $context->clinicId);
        $sourceLocation = new InventoryLocation([
            'code' => 'CONS-00',
            'name' => 'Consultorio histórico',
            'type' => 'clinic',
            'is_active' => true,
        ]);
        $sourceLocation->forceFill(['clinic_id' => $context->clinicId])->save();
        $source->update(['inventory_location_id' => $sourceLocation->id]);
        $location = new InventoryLocation([
            'code' => 'DEP-01',
            'name' => 'Depósito principal',
            'type' => 'storage',
            'is_active' => true,
        ]);
        $location->forceFill(['clinic_id' => $context->clinicId])->save();

        $this->actingAs($user)->withSession(['clinic_id' => $context->clinicId])
            ->post(route('inventory.locations.stock.store', $source), ['inventory_location_id' => $location->id])
            ->assertRedirect(route('inventory.index'));

        $this->assertDatabaseHas('inventory', [
            'product_id' => $source->product_id,
            'inventory_location_id' => $location->id,
            'current_stock' => 0,
            'available_stock' => 0,
            'clinic_id' => $context->clinicId,
        ]);
        $this->assertDatabaseHas('activity_log', ['description' => 'inventory.location.stock.created']);
    }

    public function test_a_product_cannot_receive_the_same_stock_location_twice(): void
    {
        [$user, $context] = $this->manager();
        $source = $this->inventory($user, $context->clinicId);
        $location = new InventoryLocation([
            'code' => 'ALM-01',
            'name' => 'Almacén principal',
            'type' => 'warehouse',
            'is_active' => true,
        ]);
        $location->forceFill(['clinic_id' => $context->clinicId])->save();
        $existing = new Inventory([
            'product_id' => $source->product_id,
            'inventory_location_id' => $location->id,
            'current_stock' => 0,
            'available_stock' => 0,
        ]);
        $existing->forceFill(['clinic_id' => $context->clinicId])->save();

        $this->actingAs($user)->withSession(['clinic_id' => $context->clinicId])
            ->from(route('inventory.index'))
            ->post(route('inventory.locations.stock.store', $source), ['inventory_location_id' => $location->id])
            ->assertRedirect(route('inventory.index'))
            ->assertSessionHasErrors('inventory_location_id');

        $this->assertDatabaseCount('inventory', 2);
    }

    public function test_unassigned_historical_inventory_is_assigned_before_a_new_stock_location_is_created(): void
    {
        [$user, $context] = $this->manager();
        $source = $this->inventory($user, $context->clinicId);
        $location = new InventoryLocation([
            'code' => 'HIST-01',
            'name' => 'Ubicación histórica',
            'type' => 'storage',
            'is_active' => true,
        ]);
        $location->forceFill(['clinic_id' => $context->clinicId])->save();

        $this->actingAs($user)->withSession(['clinic_id' => $context->clinicId])
            ->post(route('inventory.locations.stock.store', $source), ['inventory_location_id' => $location->id])
            ->assertRedirect(route('inventory.index'));

        $this->assertSame($location->id, $source->fresh()->inventory_location_id);
        $this->assertDatabaseCount('inventory', 1);
        $this->assertDatabaseHas('activity_log', ['description' => 'inventory.location.assigned']);
    }

    private function manager(): array
    {
        $user = User::factory()->create(['is_active' => true]);
        $context = $this->clinicalContextFor($user, ['view_inventory', 'manage_inventory']);

        return [$user, $context];
    }

    private function inventory(User $user, int $clinicId): Inventory
    {
        $product = Product::query()->create([
            'product_code' => 'LOCATION-'.uniqid(),
            'name' => 'Producto de ubicación',
            'category' => 'materiales',
            'unit_of_measure' => 'piezas',
            'minimum_stock' => 1,
            'created_by' => $user->id,
        ]);

        $inventory = new Inventory([
            'product_id' => $product->id,
            'current_stock' => 8,
            'available_stock' => 8,
            'location' => 'Ubicación histórica',
        ]);
        $inventory->forceFill(['clinic_id' => $clinicId])->save();

        return $inventory;
    }
}
