<?php

namespace App\Services;

use App\Data\InventoryMovementData;
use App\Models\Inventory;
use App\Models\InventoryMovement;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

class InventoryMovementService
{
    public function reverse(InventoryMovement $movement, User $actor): InventoryMovement
    {
        return DB::transaction(function () use ($movement, $actor): InventoryMovement {
            $original = InventoryMovement::query()->lockForUpdate()->findOrFail($movement->id);

            if ($original->reference_type === InventoryMovement::class) {
                throw new InvalidArgumentException('No se puede revertir un movimiento de reversión.');
            }

            $alreadyReversed = InventoryMovement::query()
                ->where('reference_type', InventoryMovement::class)
                ->where('reference_id', $original->id)
                ->exists();

            if ($alreadyReversed) {
                throw new InvalidArgumentException('Este movimiento ya fue revertido.');
            }

            $stockDelta = $original->stock_after - $original->stock_before;

            if ($stockDelta === 0) {
                throw new InvalidArgumentException('Este movimiento no modificó el stock y no requiere reversión.');
            }

            return $this->adjust(
                new InventoryMovementData(
                    $original->inventory_id,
                    $original->product_id,
                    $stockDelta > 0 ? 'consumption' : 'restock',
                    abs($stockDelta),
                    'Reversión del movimiento #'.$original->id.': '.($original->reason ?: $original->type),
                    $original->destination_location,
                    $original->source_location,
                    InventoryMovement::class,
                    $original->id,
                    ['reversal_of' => $original->id],
                ),
                $actor,
            );
        });
    }

    public function adjust(InventoryMovementData $data, User $actor): InventoryMovement
    {
        if ($data->quantity < 1) {
            throw new InvalidArgumentException('La cantidad debe ser mayor que cero.');
        }

        if (!in_array($data->type, ['adjustment', 'restock', 'consumption'], true)) {
            throw new InvalidArgumentException('El tipo de movimiento no es válido.');
        }

        if ($data->reason === null || trim($data->reason) === '') {
            throw new InvalidArgumentException('El motivo del movimiento es obligatorio.');
        }

        return DB::transaction(function () use ($data, $actor): InventoryMovement {
            $inventory = Inventory::query()->lockForUpdate()->findOrFail($data->inventoryId);

            if ((int) $inventory->product_id !== $data->productId) {
                throw new InvalidArgumentException('El producto no corresponde al inventario indicado.');
            }

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

            $movement = InventoryMovement::create([
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

            activity('inventory')
                ->causedBy($actor)
                ->performedOn($movement)
                ->withProperties([
                    'inventory_id' => $inventory->id,
                    'product_id' => $data->productId,
                    'type' => $data->type,
                    'quantity' => $data->quantity,
                    'stock_before' => $before,
                    'stock_after' => $after,
                    'reason' => $data->reason,
                ])
                ->log('inventory.movement.created');

            return $movement;
        });
    }
}
