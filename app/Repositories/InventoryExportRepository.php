<?php

namespace App\Repositories;

use App\Models\Inventory;
use Illuminate\Database\Eloquent\Builder;

class InventoryExportRepository
{
    public function query(array $filters): Builder
    {
        $query = Inventory::query()->with(['product', 'inventoryLocation']);

        if (! empty($filters['inventory_location_id'])) $query->where('inventory_location_id', $filters['inventory_location_id']);
        if (! empty($filters['category'])) $query->whereHas('product', fn (Builder $q) => $q->where('category', $filters['category']));
        if (($filters['stock_level'] ?? null) === 'out') $query->where('available_stock', 0);
        if (($filters['stock_level'] ?? null) === 'low') $query->whereRaw('available_stock <= (SELECT minimum_stock FROM products WHERE products.id = inventory.product_id)');
        if (($filters['stock_level'] ?? null) === 'normal') $query->whereRaw('available_stock > (SELECT minimum_stock FROM products WHERE products.id = inventory.product_id)')->where('available_stock', '>', 0);

        return $query;
    }
}
