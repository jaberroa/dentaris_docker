<?php

namespace App\Policies;

use App\Models\Supplier;
use App\Models\User;
use App\Modules\Clinics\Services\ClinicPermissionService;
use Illuminate\Auth\Access\Response;

class SupplierPolicy
{
    public function __construct(
        private readonly ClinicPermissionService $permissions,
    ) {}

    public function viewAny(User $user): bool
    {
        return $this->permissions->allows($user, 'view_suppliers');
    }

    public function view(User $user, Supplier $supplier): Response
    {
        return $this->resourceResponse($user, $supplier, 'view_suppliers');
    }

    public function create(User $user): bool
    {
        return $this->permissions->allows($user, 'manage_suppliers');
    }

    public function update(User $user, Supplier $supplier): Response
    {
        return $this->resourceResponse($user, $supplier, 'manage_suppliers');
    }

    public function delete(User $user, Supplier $supplier): Response
    {
        return $this->resourceResponse($user, $supplier, 'manage_suppliers');
    }

    private function resourceResponse(User $user, Supplier $supplier, string $permission): Response
    {
        $context = $this->permissions->currentContext();
        if ($context === null || $supplier->clinic_id === null || (int) $supplier->clinic_id !== $context->clinicId) {
            return Response::denyAsNotFound();
        }

        return $this->permissions->allows($user, $permission, $context)
            ? Response::allow()
            : Response::deny('No tienes permisos clínicos para realizar esta acción.');
    }
}
