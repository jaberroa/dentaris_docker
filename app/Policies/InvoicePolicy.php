<?php

namespace App\Policies;

use App\Models\Invoice;
use App\Models\User;

class InvoicePolicy
{
    public function view(User $user, Invoice $invoice): bool
    {
        return $user->hasPermission('view_billing');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('manage_billing');
    }

    public function update(User $user, Invoice $invoice): bool
    {
        return $user->hasPermission('manage_billing') && $invoice->isEditable();
    }

    public function delete(User $user, Invoice $invoice): bool
    {
        return $user->hasPermission('manage_billing')
            && ! $invoice->isPaid()
            && ! $invoice->payments()->exists()
            && $invoice->status !== 'cancelled';
    }

    public function send(User $user, Invoice $invoice): bool
    {
        return $user->hasPermission('manage_billing') && in_array($invoice->status, ['draft', 'sent'], true) && !$invoice->isPaid();
    }
}
