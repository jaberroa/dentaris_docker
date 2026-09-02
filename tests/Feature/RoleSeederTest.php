<?php

namespace Tests\Feature;

use App\Models\Role;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_role_receives_inventory_adjustment_permission(): void
    {
        app(RoleSeeder::class)->run();

        $admin = Role::query()->where('name', 'admin')->firstOrFail();

        $this->assertContains('adjust_inventory', $admin->permissions);
    }
}
