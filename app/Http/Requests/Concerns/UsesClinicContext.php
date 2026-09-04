<?php

namespace App\Http\Requests\Concerns;

use App\Models\User;
use App\Modules\Clinics\Data\ClinicContext;
use App\Modules\Clinics\Services\ClinicPermissionService;

trait UsesClinicContext
{
    public function clinicContext(): ?ClinicContext
    {
        $context = $this->attributes->get(ClinicContext::class)
            ?? $this->attributes->get('clinic.context');

        return $context instanceof ClinicContext ? $context : null;
    }

    protected function hasClinicPermission(string $permission): bool
    {
        $user = $this->user();
        $context = $this->clinicContext();

        return $user instanceof User
            && $context instanceof ClinicContext
            && app(ClinicPermissionService::class)->allows($user, $permission, $context);
    }
}
