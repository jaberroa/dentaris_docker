<?php

namespace App\Policies;

use App\Models\Inventory;
use App\Models\User;

class InventoryPolicy
{
    public function view(User $user, Inventory $inventory): bool
    {
        return $user->hasPermission('view_inventory');
    }

    public function update(User $user, Inventory $inventory): bool
    {
        return $user->hasPermission('manage_inventory');
    }

    public function adjust(User $user, Inventory $inventory): bool
    {
        return $user->hasPermission('adjust_inventory');
    }

    public function transfer(User $user): bool
    {
        return $user->hasPermission('manage_inventory');
    }

    public function export(User $user): bool
    {
        return $user->hasPermission('export_inventory');
    }
}
