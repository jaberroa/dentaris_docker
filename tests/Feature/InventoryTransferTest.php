<?php

namespace Tests\Feature;

use App\Models\Inventory;
use App\Models\Product;
use App\Models\User;
use App\Services\InventoryMovementService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\Concerns\InteractsWithClinicalContext;
use Tests\TestCase;

class InventoryTransferTest extends TestCase
{
    use RefreshDatabase;
    use InteractsWithClinicalContext;

    public function test_transfer_moves_available_stock_and_records_linked_history(): void
    {
        [$user, $source, $destination, $context] = $this->fixture();

        $transfer = app(InventoryMovementService::class)->transfer(
            $source->id,
            $destination->id,
            4,
            'Reposición del consultorio 1',
            $user,
            $context,
        );

        $this->assertSame(6, $source->fresh()->current_stock);
        $this->assertSame(4, $source->fresh()->available_stock);
        $this->assertSame(7, $destination->fresh()->current_stock);
        $this->assertSame(6, $destination->fresh()->available_stock);
        $this->assertSame('transfer_out', $transfer['outgoing']->type);
        $this->assertSame('transfer_in', $transfer['incoming']->type);
        $this->assertSame($transfer['outgoing']->metadata['transfer_id'], $transfer['incoming']->metadata['transfer_id']);
        $this->assertSame($transfer['incoming']->id, $transfer['outgoing']->metadata['counterpart_movement_id']);
        $this->assertSame($transfer['outgoing']->id, $transfer['incoming']->metadata['counterpart_movement_id']);
        $this->assertDatabaseCount('inventory_movements', 2);
        $this->assertDatabaseCount('activity_log', 2);
    }

    public function test_transfer_rejects_quantity_reserved_or_unavailable_at_source(): void
    {
        [$user, $source, $destination, $context] = $this->fixture();

        try {
            app(InventoryMovementService::class)->transfer(
                $source->id,
                $destination->id,
                9,
                'Intento sin stock disponible',
                $user,
                $context,
            );
            $this->fail('La transferencia debió rechazar stock reservado o no disponible.');
        } catch (RuntimeException) {
            // La excepción es el comportamiento esperado; se valida que no haya efectos parciales.
        }

        $this->assertSame(10, $source->fresh()->current_stock);
        $this->assertSame(3, $destination->fresh()->current_stock);
        $this->assertDatabaseCount('inventory_movements', 0);
    }

    public function test_user_with_inventory_management_permission_can_transfer_over_http(): void
    {
        [$user, $source, $destination, $context] = $this->fixture();

        $this->actingAs($user)->withSession(['clinic_id' => $context->clinicId])
            ->postJson(route('inventory.transfer'), [
                'inventory_id' => $source->id,
                'destination_inventory_id' => $destination->id,
                'quantity' => 4,
                'reason' => 'Transferencia HTTP',
            ])
            ->assertCreated()
            ->assertJsonPath('data.outgoing.type', 'transfer_out')
            ->assertJsonPath('data.incoming.type', 'transfer_in');

        $this->assertDatabaseCount('inventory_movements', 2);
    }

    public function test_transfer_history_cannot_be_reversed_as_a_single_movement(): void
    {
        [$user, $source, $destination, $context] = $this->fixture();
        $transfer = app(InventoryMovementService::class)->transfer($source->id, $destination->id, 2, 'Traslado interno', $user, $context);
        $this->bindClinicalContext($context, $user);

        $this->assertFalse($user->can('reverse', $transfer['outgoing']));
        $this->assertFalse($user->can('reverse', $transfer['incoming']));
        $this->assertDatabaseHas('inventory_movements', [
            'id' => $transfer['outgoing']->id,
            'type' => 'transfer_out',
        ]);
    }

    private function fixture(): array
    {
        $user = User::factory()->create(['is_active' => true]);
        $context = $this->clinicalContextFor($user, ['manage_inventory']);

        $product = Product::query()->create([
            'product_code' => 'TRANSFER-'.uniqid(),
            'name' => 'Material transferible',
            'category' => 'materiales',
            'unit_of_measure' => 'piezas',
            'minimum_stock' => 1,
            'created_by' => $user->id,
        ]);
        $source = new Inventory([
            'product_id' => $product->id,
            'current_stock' => 10,
            'reserved_stock' => 2,
            'available_stock' => 8,
            'location' => 'Depósito principal',
        ]);
        $source->forceFill(['clinic_id' => $context->clinicId])->save();
        $destination = new Inventory([
            'product_id' => $product->id,
            'current_stock' => 3,
            'reserved_stock' => 1,
            'available_stock' => 2,
            'location' => 'Consultorio 1',
        ]);
        $destination->forceFill(['clinic_id' => $context->clinicId])->save();

        return [$user, $source, $destination, $context];
    }
}
