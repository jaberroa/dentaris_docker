<?php

namespace App\Services;

use App\Data\InventoryMovementData;
use App\Models\Inventory;
use App\Models\InventoryMovement;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;
use RuntimeException;

class InventoryMovementService
{
    public function transfer(
        int $sourceInventoryId,
        int $destinationInventoryId,
        int $quantity,
        string $reason,
        User $actor,
    ): array {
        if ($sourceInventoryId === $destinationInventoryId) {
            throw new InvalidArgumentException('El origen y el destino deben ser inventarios distintos.');
        }

        if ($quantity < 1) {
            throw new InvalidArgumentException('La cantidad debe ser mayor que cero.');
        }

        if (trim($reason) === '') {
            throw new InvalidArgumentException('El motivo de la transferencia es obligatorio.');
        }

        return DB::transaction(function () use ($sourceInventoryId, $destinationInventoryId, $quantity, $reason, $actor): array {
            $inventories = Inventory::query()
                ->whereIn('id', [$sourceInventoryId, $destinationInventoryId])
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $source = $inventories->get($sourceInventoryId);
            $destination = $inventories->get($destinationInventoryId);

            if ($source === null || $destination === null) {
                throw new InvalidArgumentException('No se encontró el inventario de origen o destino.');
            }

            if ((int) $source->product_id !== (int) $destination->product_id) {
                throw new InvalidArgumentException('El inventario de destino debe corresponder al mismo producto.');
            }

            if ((int) $source->available_stock < $quantity) {
                throw new RuntimeException('No hay stock disponible suficiente para completar la transferencia.');
            }

            $sourceBefore = (int) $source->current_stock;
            $sourceAfter = $sourceBefore - $quantity;
            $destinationBefore = (int) $destination->current_stock;
            $destinationAfter = $destinationBefore + $quantity;
            $transferId = (string) Str::uuid();
            $sourceLocation = $source->location ?: 'Ubicación de origen no definida';
            $destinationLocation = $destination->location ?: 'Ubicación de destino no definida';

            $source->update([
                'current_stock' => $sourceAfter,
                'available_stock' => max(0, $sourceAfter - (int) $source->reserved_stock),
            ]);
            $destination->update([
                'current_stock' => $destinationAfter,
                'available_stock' => max(0, $destinationAfter - (int) $destination->reserved_stock),
            ]);

            $outgoing = $this->recordMovement(
                $source,
                new InventoryMovementData(
                    $source->id,
                    $source->product_id,
                    'transfer_out',
                    $quantity,
                    $reason,
                    $sourceLocation,
                    $destinationLocation,
                    metadata: [
                        'transfer_id' => $transferId,
                        'counterpart_inventory_id' => $destination->id,
                    ],
                ),
                $actor,
                $sourceBefore,
                $sourceAfter,
            );

            $incoming = $this->recordMovement(
                $destination,
                new InventoryMovementData(
                    $destination->id,
                    $destination->product_id,
                    'transfer_in',
                    $quantity,
                    $reason,
                    $sourceLocation,
                    $destinationLocation,
                    metadata: [
                        'transfer_id' => $transferId,
                        'counterpart_inventory_id' => $source->id,
                    ],
                ),
                $actor,
                $destinationBefore,
                $destinationAfter,
            );

            $outgoing->update(['metadata' => [...$outgoing->metadata, 'counterpart_movement_id' => $incoming->id]]);
            $incoming->update(['metadata' => [...$incoming->metadata, 'counterpart_movement_id' => $outgoing->id]]);

            return [
                'outgoing' => $outgoing->fresh(),
                'incoming' => $incoming->fresh(),
            ];
        });
    }

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

            return $this->recordMovement($inventory, $data, $actor, $before, $after);
        });
    }

    private function recordMovement(
        Inventory $inventory,
        InventoryMovementData $data,
        User $actor,
        int $before,
        int $after,
    ): InventoryMovement {
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
                'source_location' => $data->sourceLocation,
                'destination_location' => $data->destinationLocation,
                'metadata' => $data->metadata,
            ])
            ->log('inventory.movement.created');

        return $movement;
    }
}
