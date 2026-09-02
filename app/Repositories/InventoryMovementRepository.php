<?php

namespace App\Repositories;

use App\Models\InventoryMovement;
use Illuminate\Database\Eloquent\Builder;

class InventoryMovementRepository
{
    public function query(): Builder
    {
        return InventoryMovement::query()->with(['inventory', 'product', 'user']);
    }

    public function forInventory(int $inventoryId): Builder
    {
        return $this->query()->where('inventory_id', $inventoryId)->latest();
    }

    public function forProduct(int $productId): Builder
    {
        return $this->query()->where('product_id', $productId)->latest();
    }
}
