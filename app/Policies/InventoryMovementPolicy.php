<?php

namespace App\Policies;

use App\Models\InventoryMovement;
use App\Models\User;
use App\Modules\Clinics\Services\ClinicPermissionService;
use Illuminate\Auth\Access\Response;

class InventoryMovementPolicy
{
    public function __construct(
        private readonly ClinicPermissionService $permissions,
    ) {
    }

    public function reverse(User $user, InventoryMovement $movement): Response
    {
        $context = $this->permissions->currentContext();

        if ($context === null
            || $movement->clinic_id === null
            || (int) $movement->clinic_id !== $context->clinicId) {
            return Response::denyAsNotFound();
        }

        if (! $this->permissions->allows($user, 'adjust_inventory', $context)) {
            return Response::deny('No tienes permisos clínicos para realizar esta acción.');
        }

        return isset($movement->metadata['transfer_id'])
            ? Response::deny('Los movimientos de transferencia deben revertirse desde su operación asociada.')
            : Response::allow();
    }
}
