<?php

namespace Tests\Unit;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Tests\TestCase;

class AuthorizationResolutionTest extends TestCase
{
    public function test_user_receives_permission_from_any_assigned_role(): void
    {
        $user = $this->userWithRoles(
            $this->role('receptionist', ['view_billing']),
            $this->role('inventory_manager', ['manage_inventory'])
        );

        $this->assertTrue($user->hasPermission('view_billing'));
        $this->assertTrue($user->can('manage_inventory'));
        $this->assertFalse($user->hasPermission('manage_billing'));
    }

    public function test_super_admin_requires_explicit_permission_until_bypass_is_defined(): void
    {
        $user = $this->userWithRoles($this->role('super_admin', ['view_billing']));

        $this->assertTrue($user->hasPermission('view_billing'));
        $this->assertFalse($user->hasPermission('manage_inventory'));
    }

    public function test_current_admin_role_is_universal_bypass_and_is_documented_gap(): void
    {
        $user = $this->userWithRoles($this->role('admin', []));

        $this->assertTrue($user->hasPermission('manage_billing'));
        $this->assertTrue($user->can('adjust_inventory'));
    }

    public function test_user_without_roles_has_no_permissions(): void
    {
        $user = $this->userWithRoles();

        $this->assertFalse($user->hasPermission('view_billing'));
        $this->assertFalse($user->can('manage_inventory'));
    }

    private function role(string $name, array $permissions): Role
    {
        return (new Role())->forceFill([
            'name' => $name,
            'permissions' => $permissions,
        ]);
    }

    private function userWithRoles(Role ...$roles): User
    {
        return (new User())->setRelation('roles', new Collection($roles));
    }
}
