<?php

namespace App\Policies;

use App\Models\Invoice;
use App\Models\User;
use App\Modules\Clinics\Services\ClinicPermissionService;
use Illuminate\Auth\Access\Response;

class InvoicePolicy
{
    public function __construct(
        private readonly ClinicPermissionService $permissions,
    ) {
    }

    public function viewAny(User $user): bool
    {
        return $this->permissions->allows($user, 'view_billing');
    }

    public function view(User $user, Invoice $invoice): Response
    {
        return $this->resourceResponse($user, $invoice, 'view_billing');
    }

    public function create(User $user): bool
    {
        return $this->permissions->allows($user, 'manage_billing');
    }

    public function update(User $user, Invoice $invoice): Response
    {
        $authorization = $this->resourceResponse($user, $invoice, 'manage_billing');

        if ($authorization->denied()) {
            return $authorization;
        }

        return $invoice->isEditable($this->permissions->currentContext())
            ? Response::allow()
            : Response::deny('Solo se puede editar una factura en borrador sin pagos.');
    }

    public function delete(User $user, Invoice $invoice): Response
    {
        $authorization = $this->resourceResponse($user, $invoice, 'manage_billing');

        if ($authorization->denied()) {
            return $authorization;
        }

        return ! $invoice->isPaid()
            && ! $invoice->payments()->forClinic($this->permissions->currentContext())->exists()
            && $invoice->status !== 'cancelled'
                ? Response::allow()
                : Response::deny('La factura ya no puede anularse.');
    }

    public function send(User $user, Invoice $invoice): Response
    {
        $authorization = $this->resourceResponse($user, $invoice, 'manage_billing');

        if ($authorization->denied()) {
            return $authorization;
        }

        return in_array($invoice->status, ['draft', 'sent'], true)
            && ! $invoice->isPaid()
                ? Response::allow()
                : Response::deny('La factura ya no puede enviarse.');
    }

    private function resourceResponse(User $user, Invoice $invoice, string $permission): Response
    {
        $context = $this->permissions->currentContext();

        if ($context === null
            || $invoice->clinic_id === null
            || (int) $invoice->clinic_id !== $context->clinicId) {
            return Response::denyAsNotFound();
        }

        return $this->permissions->allows($user, $permission, $context)
            ? Response::allow()
            : Response::deny('No tienes permisos clínicos para realizar esta acción.');
    }
}
