<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;
use App\Modules\Clinics\Services\ClinicPermissionService;
use Illuminate\Auth\Access\Response;

class ProductPolicy
{
    public function __construct(
        private readonly ClinicPermissionService $permissions,
    ) {}

    public function viewAny(User $user): bool
    {
        return $this->permissions->allows($user, 'view_inventory');
    }

    public function view(User $user, Product $product): Response
    {
        return $this->resourceResponse($user, $product, 'view_inventory');
    }

    public function create(User $user): bool
    {
        return $this->permissions->allows($user, 'manage_inventory');
    }

    public function update(User $user, Product $product): Response
    {
        return $this->resourceResponse($user, $product, 'manage_inventory');
    }

    public function delete(User $user, Product $product): Response
    {
        return $this->resourceResponse($user, $product, 'manage_inventory');
    }

    private function resourceResponse(User $user, Product $product, string $permission): Response
    {
        $context = $this->permissions->currentContext();
        if ($context === null || $product->clinic_id === null || (int) $product->clinic_id !== $context->clinicId) {
            return Response::denyAsNotFound();
        }

        return $this->permissions->allows($user, $permission, $context)
            ? Response::allow()
            : Response::deny('No tienes permisos clínicos para realizar esta acción.');
    }
}
