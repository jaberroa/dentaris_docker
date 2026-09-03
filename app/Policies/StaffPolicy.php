<?php

namespace App\Policies;

use App\Models\Staff;
use App\Models\User;
use App\Modules\Clinics\Services\ClinicPermissionService;

class StaffPolicy
{
    public function __construct(
        private readonly ClinicPermissionService $permissions,
    ) {
    }

    public function viewAny(User $user): bool
    {
        return $this->permissions->allows($user, 'view_staff');
    }

    public function view(User $user, Staff $staff): bool
    {
        return $this->permissions->allowsResource($user, $staff, 'view_staff');
    }

    public function create(User $user): bool
    {
        return $this->permissions->allows($user, 'manage_staff');
    }

    public function update(User $user, Staff $staff): bool
    {
        return $this->permissions->allowsResource($user, $staff, 'manage_staff');
    }

    public function delete(User $user, Staff $staff): bool
    {
        return $this->permissions->allowsResource($user, $staff, 'manage_staff');
    }

    public function restore(User $user, Staff $staff): bool
    {
        return $this->permissions->allowsResource($user, $staff, 'manage_staff');
    }

    public function forceDelete(User $user, Staff $staff): bool
    {
        return $this->permissions->allowsResource($user, $staff, 'manage_staff');
    }

    public function exportAny(User $user): bool
    {
        return $this->permissions->allows($user, 'view_staff');
    }

    public function export(User $user, Staff $staff): bool
    {
        return $this->permissions->allowsResource($user, $staff, 'view_staff');
    }
}
