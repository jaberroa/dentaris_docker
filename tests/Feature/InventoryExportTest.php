<?php

namespace Tests\Feature;

use App\Models\Inventory;
use App\Models\InventoryLocation;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithClinicalContext;
use Tests\TestCase;

class InventoryExportTest extends TestCase
{
    use RefreshDatabase;
    use InteractsWithClinicalContext;

    public function test_user_with_export_permission_downloads_inventory_csv_with_location(): void
    {
        [$user, $location, $context] = $this->fixture(['export_inventory']);

        $response = $this->actingAs($user)->withSession(['clinic_id' => $context->clinicId])
            ->post(route('inventory.export'), ['inventory_location_id' => $location->id]);

        $response->assertOk()->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('Consultorio 1', $response->streamedContent());
        $this->assertDatabaseHas('activity_log', ['description' => 'inventory.exported']);
    }

    public function test_user_without_export_permission_is_rejected(): void
    {
        [$user, , $context] = $this->fixture(['manage_inventory']);
        $this->actingAs($user)->withSession(['clinic_id' => $context->clinicId])
            ->post(route('inventory.export'))->assertForbidden();
    }

    public function test_user_with_export_permission_can_download_excel_and_pdf(): void
    {
        [$user, , $context] = $this->fixture(['export_inventory']);

        $this->actingAs($user)->withSession(['clinic_id' => $context->clinicId])
            ->post(route('inventory.export'), ['format' => 'xlsx'])->assertOk();
        $this->actingAs($user)->withSession(['clinic_id' => $context->clinicId])
            ->post(route('inventory.export'), ['format' => 'pdf'])->assertOk();
    }

    private function fixture(array $permissions): array
    {
        $user = User::factory()->create(['is_active' => true]);
        $context = $this->clinicalContextFor($user, $permissions);
        $location = new InventoryLocation(['code' => 'CONS-01', 'name' => 'Consultorio 1', 'type' => 'clinic', 'is_active' => true]);
        $location->forceFill(['clinic_id' => $context->clinicId])->save();
        $product = Product::query()->create(['product_code' => 'EXP-'.uniqid(), 'name' => 'Producto exportable', 'category' => 'materiales', 'unit_of_measure' => 'piezas', 'minimum_stock' => 2, 'created_by' => $user->id]);
        $inventory = new Inventory(['product_id' => $product->id, 'inventory_location_id' => $location->id, 'current_stock' => 5, 'available_stock' => 5, 'average_cost' => 4.50, 'location' => $location->name]);
        $inventory->forceFill(['clinic_id' => $context->clinicId])->save();

        return [$user, $location, $context];
    }
}
