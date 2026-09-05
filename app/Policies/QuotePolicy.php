<?php

namespace App\Policies;

use App\Models\Quote;
use App\Models\User;
use App\Modules\Clinics\Services\ClinicPermissionService;
use Illuminate\Auth\Access\Response;

class QuotePolicy
{
    public function __construct(
        private readonly ClinicPermissionService $permissions,
    ) {}

    public function viewAny(User $user): bool
    {
        return $this->permissions->allows($user, 'view_quotes');
    }

    public function view(User $user, Quote $quote): Response
    {
        return $this->resourceResponse($user, $quote, 'view_quotes');
    }

    public function create(User $user): bool
    {
        return $this->permissions->allows($user, 'manage_quotes');
    }

    public function update(User $user, Quote $quote): Response
    {
        return $this->resourceResponse($user, $quote, 'manage_quotes');
    }

    public function delete(User $user, Quote $quote): Response
    {
        return $this->resourceResponse($user, $quote, 'manage_quotes');
    }

    private function resourceResponse(User $user, Quote $quote, string $permission): Response
    {
        $context = $this->permissions->currentContext();
        if ($context === null || $quote->clinic_id === null || (int) $quote->clinic_id !== $context->clinicId) {
            return Response::denyAsNotFound();
        }

        return $this->permissions->allows($user, $permission, $context)
            ? Response::allow()
            : Response::deny('No tienes permisos clínicos para realizar esta acción.');
    }
}
