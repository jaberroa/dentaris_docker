<?php

namespace App\Policies;

use App\Models\InventoryMovement;
use App\Models\User;

class InventoryMovementPolicy
{
    public function reverse(User $user, InventoryMovement $movement): bool
    {
        return $user->hasPermission('adjust_inventory');
    }
}
