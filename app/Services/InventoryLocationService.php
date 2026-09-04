<?php

namespace App\Services;

use App\Models\Inventory;
use App\Models\InventoryLocation;
use App\Models\User;
use App\Modules\Clinics\Data\ClinicContext;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class InventoryLocationService
{
    public function assignOrCreateStockLocation(
        Inventory $inventory,
        int $locationId,
        User $actor,
        ClinicContext $context,
    ): array
    {
        return DB::transaction(function () use ($inventory, $locationId, $actor, $context): array {
            $source = Inventory::query()->forClinic($context)->lockForUpdate()->findOrFail($inventory->id);
            $location = InventoryLocation::query()->forClinic($context)->where('is_active', true)->lockForUpdate()->findOrFail($locationId);

            if ((int) $source->inventory_location_id === $location->id) {
                throw new InvalidArgumentException('El producto ya está asignado a esta ubicación.');
            }

            $exists = Inventory::query()
                ->forClinic($context)
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

            $destination = new Inventory([
                'product_id' => $source->product_id,
                'inventory_location_id' => $location->id,
                'current_stock' => 0,
                'reserved_stock' => 0,
                'available_stock' => 0,
                'average_cost' => $source->average_cost,
                'location' => $location->name,
            ]);
            $destination->forceFill(['clinic_id' => $context->clinicId])->save();

            activity('inventory')
                ->causedBy($actor)
                ->performedOn($destination)
                ->withProperties([
                    'product_id' => $source->product_id,
                    'clinic_id' => $context->clinicId,
                    'inventory_location_id' => $location->id,
                    'inventory_location_name' => $location->name,
                ])
                ->log('inventory.location.stock.created');

            return ['inventory' => $destination, 'created' => true];
        });
    }
}
