<?php

namespace App\Policies;

use App\Models\Inventory;
use App\Models\User;
use App\Modules\Clinics\Services\ClinicPermissionService;
use Illuminate\Auth\Access\Response;

class InventoryPolicy
{
    public function __construct(
        private readonly ClinicPermissionService $permissions,
    ) {
    }

    public function viewAny(User $user): bool
    {
        return $this->permissions->allows($user, 'view_inventory');
    }

    public function view(User $user, Inventory $inventory): Response
    {
        return $this->resourceResponse($user, $inventory, 'view_inventory');
    }

    public function update(User $user, Inventory $inventory): Response
    {
        return $this->resourceResponse($user, $inventory, 'manage_inventory');
    }

    public function adjust(User $user, Inventory $inventory): Response
    {
        return $this->resourceResponse($user, $inventory, 'adjust_inventory');
    }

    public function transfer(User $user): bool
    {
        return $this->permissions->allows($user, 'manage_inventory');
    }

    public function export(User $user): bool
    {
        return $this->permissions->allows($user, 'export_inventory');
    }

    private function resourceResponse(User $user, Inventory $inventory, string $permission): Response
    {
        $context = $this->permissions->currentContext();

        if ($context === null
            || $inventory->clinic_id === null
            || (int) $inventory->clinic_id !== $context->clinicId) {
            return Response::denyAsNotFound();
        }

        return $this->permissions->allows($user, $permission, $context)
            ? Response::allow()
            : Response::deny('No tienes permisos clínicos para realizar esta acción.');
    }
}
