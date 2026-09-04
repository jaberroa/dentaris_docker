<?php

namespace App\Policies;

use App\Models\Payment;
use App\Models\User;
use App\Modules\Clinics\Services\ClinicPermissionService;
use Illuminate\Auth\Access\Response;

class PaymentPolicy
{
    public function __construct(
        private readonly ClinicPermissionService $permissions,
    ) {}

    public function viewAny(User $user): bool
    {
        return $this->permissions->allows($user, 'view_payments');
    }

    public function view(User $user, Payment $payment): Response
    {
        return $this->resourceResponse($user, $payment, 'view_payments');
    }

    public function create(User $user): bool
    {
        return $this->permissions->allows($user, 'manage_payments');
    }

    public function update(User $user, Payment $payment): Response
    {
        return $this->resourceResponse($user, $payment, 'manage_payments');
    }

    public function delete(User $user, Payment $payment): Response
    {
        return $this->resourceResponse($user, $payment, 'manage_payments');
    }

    private function resourceResponse(User $user, Payment $payment, string $permission): Response
    {
        $context = $this->permissions->currentContext();

        if ($context === null
            || $payment->clinic_id === null
            || (int) $payment->clinic_id !== $context->clinicId) {
            return Response::denyAsNotFound();
        }

        return $this->permissions->allows($user, $permission, $context)
            ? Response::allow()
            : Response::deny('No tienes permisos clínicos para realizar esta acción.');
    }
}
