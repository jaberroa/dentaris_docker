<?php

namespace Tests\Feature;

use App\Http\Requests\Inventory\CreateInventoryAdjustmentRequest;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\User;
use App\Modules\Clinics\Data\ClinicContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Gate;
use Tests\Concerns\InteractsWithClinicalContext;
use Tests\TestCase;

class InventoryAdjustmentHttpTest extends TestCase
{
    use RefreshDatabase;
    use InteractsWithClinicalContext;

    public function test_user_with_adjust_permission_can_adjust_inventory_over_http(): void
    {
        [$user, $inventory, $context] = $this->fixture(['adjust_inventory']);

        $this->assertAdjustmentAuthorization($user, $inventory, $context);

        $response = $this->actingAs($user)->withSession(['clinic_id' => $context->clinicId])->postJson(
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
        [$user, $inventory, $context] = $this->fixture(['manage_inventory']);

        $this->actingAs($user)->withSession(['clinic_id' => $context->clinicId])
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
        [$user, $inventory, $context] = $this->fixture(['adjust_inventory']);

        $this->assertAdjustmentAuthorization($user, $inventory, $context);

        $this->actingAs($user)->withSession(['clinic_id' => $context->clinicId])
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
        $user = User::factory()->create(['is_active' => true]);
        $context = $this->clinicalContextFor($user, $permissions);

        $product = Product::query()->create([
            'product_code' => 'HTTP-' . uniqid(),
            'name' => 'Producto HTTP',
            'category' => 'materiales',
            'unit_of_measure' => 'piezas',
            'minimum_stock' => 1,
            'created_by' => $user->id,
        ]);
        $inventory = new Inventory([
            'product_id' => $product->id,
            'current_stock' => 0,
            'available_stock' => 0,
        ]);
        $inventory->forceFill(['clinic_id' => $context->clinicId])->save();

        return [$user, $inventory, $context];
    }

    private function adjustmentRequestFor(User $user, Inventory $inventory, ClinicContext $context): CreateInventoryAdjustmentRequest
    {
        $request = CreateInventoryAdjustmentRequest::create('/inventory/'.$inventory->id.'/adjust', 'POST');
        $route = new Route('POST', 'inventory/{inventory}', static fn () => null);
        $route->bind($request);
        $route->setParameter('inventory', $inventory);

        $request->setRouteResolver(static fn () => $route);
        $request->setUserResolver(static fn () => $user);
        $request->attributes->set(ClinicContext::class, $context);
        $request->attributes->set('clinic.context', $context);

        return $request;
    }

    private function assertAdjustmentAuthorization(User $user, Inventory $inventory, ClinicContext $context): void
    {
        $this->bindClinicalContext($context, $user);
        $this->assertTrue(Gate::forUser($user)->allows('adjust', $inventory));
        $this->assertTrue($this->adjustmentRequestFor($user, $inventory, $context)->authorize());
    }
}
