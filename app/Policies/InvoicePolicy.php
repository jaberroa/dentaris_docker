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
        return $user->hasPermission('manage_billing') && !$invoice->isPaid();
    }

    public function delete(User $user, Invoice $invoice): bool
    {
        return $user->hasPermission('manage_billing') && !$invoice->isPaid();
    }

    public function send(User $user, Invoice $invoice): bool
    {
        return $user->hasPermission('manage_billing') && !$invoice->isPaid();
    }
}
