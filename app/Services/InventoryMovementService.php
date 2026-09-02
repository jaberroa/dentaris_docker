<?php

namespace App\Services;

use App\Data\InventoryMovementData;
use App\Models\Inventory;
use App\Models\InventoryMovement;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class InventoryMovementService
{
    public function adjust(InventoryMovementData $data, User $actor): InventoryMovement
    {
        return DB::transaction(function () use ($data, $actor): InventoryMovement {
            $inventory = Inventory::query()->lockForUpdate()->findOrFail($data->inventoryId);
            $before = (int) $inventory->current_stock;
            $after = $data->type === 'adjustment'
                ? $data->quantity
                : $before + ($data->type === 'restock' ? $data->quantity : -$data->quantity);

            if ($after < 0) {
                throw new RuntimeException('El stock resultante no puede ser negativo.');
            }

            $inventory->update([
                'current_stock' => $after,
                'available_stock' => max(0, $after - (int) $inventory->reserved_stock),
            ]);

            return InventoryMovement::create([
                'inventory_id' => $inventory->id,
                'product_id' => $data->productId,
                'user_id' => $actor->id,
                'type' => $data->type,
                'quantity' => $data->quantity,
                'stock_before' => $before,
                'stock_after' => $after,
                'reason' => $data->reason,
                'source_location' => $data->sourceLocation,
                'destination_location' => $data->destinationLocation,
                'reference_type' => $data->referenceType,
                'reference_id' => $data->referenceId,
                'metadata' => $data->metadata,
            ]);
        });
    }
}
