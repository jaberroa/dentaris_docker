<?php

namespace App\Services;

use App\Models\Inventory;
use App\Models\InventoryLocation;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class InventoryLocationService
{
    public function assignOrCreateStockLocation(Inventory $inventory, int $locationId, User $actor): array
    {
        return DB::transaction(function () use ($inventory, $locationId, $actor): array {
            $source = Inventory::query()->lockForUpdate()->findOrFail($inventory->id);
            $location = InventoryLocation::query()->where('is_active', true)->lockForUpdate()->findOrFail($locationId);

            if ((int) $source->inventory_location_id === $location->id) {
                throw new InvalidArgumentException('El producto ya está asignado a esta ubicación.');
            }

            $exists = Inventory::query()
                ->where('product_id', $source->product_id)
                ->where('inventory_location_id', $location->id)
                ->exists();

            if ($exists) {
                throw new InvalidArgumentException('Este producto ya tiene inventario en la ubicación seleccionada.');
            }

            if ($source->inventory_location_id === null) {
                $source->update([
                    'inventory_location_id' => $location->id,
                    'location' => $location->name,
                ]);

                activity('inventory')
                    ->causedBy($actor)
                    ->performedOn($source)
                    ->withProperties([
                        'product_id' => $source->product_id,
                        'inventory_location_id' => $location->id,
                        'inventory_location_name' => $location->name,
                    ])
                    ->log('inventory.location.assigned');

                return ['inventory' => $source->fresh(), 'created' => false];
            }

            $destination = Inventory::query()->create([
                'product_id' => $source->product_id,
                'inventory_location_id' => $location->id,
                'current_stock' => 0,
                'reserved_stock' => 0,
                'available_stock' => 0,
                'average_cost' => $source->average_cost,
                'location' => $location->name,
            ]);

            activity('inventory')
                ->causedBy($actor)
                ->performedOn($destination)
                ->withProperties([
                    'product_id' => $source->product_id,
                    'inventory_location_id' => $location->id,
                    'inventory_location_name' => $location->name,
                ])
                ->log('inventory.location.stock.created');

            return ['inventory' => $destination, 'created' => true];
        });
    }
}
