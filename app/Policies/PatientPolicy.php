<?php

namespace App\Policies;

use App\Models\Patient;
use App\Models\User;
use App\Modules\Clinics\Services\ClinicPermissionService;

class PatientPolicy
{
    public function __construct(
        private readonly ClinicPermissionService $permissions,
    ) {
    }

    public function viewAny(User $user): bool
    {
        return $this->permissions->allows($user, 'view_patients');
    }

    public function view(User $user, Patient $patient): bool
    {
        return $this->permissions->allowsResource($user, $patient, 'view_patients');
    }

    public function create(User $user): bool
    {
        return $this->permissions->allows($user, 'manage_patients');
    }

    public function update(User $user, Patient $patient): bool
    {
        return $this->permissions->allowsResource($user, $patient, 'manage_patients');
    }

    public function delete(User $user, Patient $patient): bool
    {
        return $this->permissions->allowsResource($user, $patient, 'manage_patients');
    }

    public function restore(User $user, Patient $patient): bool
    {
        return $this->permissions->allowsResource($user, $patient, 'manage_patients');
    }

    public function forceDelete(User $user, Patient $patient): bool
    {
        return $this->permissions->allowsResource($user, $patient, 'manage_patients');
    }

    public function exportAny(User $user): bool
    {
        return $this->permissions->allows($user, 'view_patients');
    }

    public function export(User $user, Patient $patient): bool
    {
        return $this->permissions->allowsResource($user, $patient, 'view_patients');
    }
}
