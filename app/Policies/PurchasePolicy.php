<?php

namespace App\Policies;

use App\Models\Purchase;
use App\Models\User;
use App\Modules\Clinics\Services\ClinicPermissionService;
use Illuminate\Auth\Access\Response;

class PurchasePolicy
{
    public function __construct(
        private readonly ClinicPermissionService $permissions,
    ) {}

    public function viewAny(User $user): bool
    {
        return $this->permissions->allows($user, 'view_purchases');
    }

    public function view(User $user, Purchase $purchase): Response
    {
        return $this->resourceResponse($user, $purchase, 'view_purchases');
    }

    public function create(User $user): bool
    {
        return $this->permissions->allows($user, 'manage_purchases');
    }

    public function update(User $user, Purchase $purchase): Response
    {
        return $this->resourceResponse($user, $purchase, 'manage_purchases');
    }

    public function delete(User $user, Purchase $purchase): Response
    {
        return $this->resourceResponse($user, $purchase, 'manage_purchases');
    }

    private function resourceResponse(User $user, Purchase $purchase, string $permission): Response
    {
        $context = $this->permissions->currentContext();
        if ($context === null || $purchase->clinic_id === null || (int) $purchase->clinic_id !== $context->clinicId) {
            return Response::denyAsNotFound();
        }

        return $this->permissions->allows($user, $permission, $context)
            ? Response::allow()
            : Response::deny('No tienes permisos clínicos para realizar esta acción.');
    }
}
