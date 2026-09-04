<?php

namespace App\Repositories;

use App\Models\InventoryMovement;
use App\Modules\Clinics\Data\ClinicContext;
use Illuminate\Database\Eloquent\Builder;

class InventoryMovementRepository
{
    public function query(ClinicContext $context): Builder
    {
        return InventoryMovement::query()->forClinic($context)->with(['inventory', 'product', 'user']);
    }

    public function forInventory(int $inventoryId, ClinicContext $context): Builder
    {
        return $this->query($context)->where('inventory_id', $inventoryId)->latest();
    }

    public function forProduct(int $productId, ClinicContext $context): Builder
    {
        return $this->query($context)->where('product_id', $productId)->latest();
    }
}
