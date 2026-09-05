<?php

namespace Tests\Feature\Clinics;

use App\Models\User;
use Database\Seeders\DemoDataSeeder;
use Database\Seeders\DoctorUsersSeeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminLoginSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_does_not_publish_demo_accounts_or_passwords(): void
    {
        $response = $this->get('/login');

        $response->assertOk()
            ->assertDontSee('Acceso Rápido')
            ->assertDontSee('Credenciales de prueba')
            ->assertDontSee('admin@dentaris.com')
            ->assertDontSee('quickLogin(');
    }

    public function test_active_verified_existing_user_can_authenticate_without_being_recreated(): void
    {
        $plainTextPassword = Str::password(32);
        $user = User::factory()->create([
            'email' => 'existing-admin@example.test',
            'password' => Hash::make($plainTextPassword),
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $originalId = $user->id;
        $originalHash = $user->password;

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => $plainTextPassword,
        ]);

        $response->assertRedirect(route('clinics.select'));
        $this->assertAuthenticatedAs($user);
        $this->assertSame(1, User::query()->where('email', $user->email)->count());
        $this->assertSame($originalId, User::query()->where('email', $user->email)->value('id'));
        $this->assertSame($originalHash, User::query()->where('email', $user->email)->value('password'));
    }

    public function test_guest_is_redirected_to_the_credential_free_login_form(): void
    {
        $this->get('/dashboard')->assertRedirect(route('login'));

        $this->get('/login')
            ->assertOk()
            ->assertDontSee('Credenciales de prueba')
            ->assertDontSee('admin@dentaris.com');
    }

    public function test_seeders_preserve_existing_identity_and_password_hashes(): void
    {
        $adminPassword = Str::password(32);
        $admin = User::factory()->create([
            'name' => 'Existing Administrator',
            'email' => 'admin@dentaris.com',
            'password' => Hash::make($adminPassword),
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $doctorPassword = Str::password(32);
        $doctor = User::factory()->create([
            'name' => 'Existing Doctor',
            'email' => 'carlos.mendoza@dentaris.com',
            'password' => Hash::make($doctorPassword),
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $adminHash = $admin->password;
        $doctorHash = $doctor->password;

        foreach ([RoleSeeder::class, UserSeeder::class, DemoDataSeeder::class, DoctorUsersSeeder::class] as $seeder) {
            $this->assertSame(0, Artisan::call('db:seed', [
                '--class' => $seeder,
                '--no-interaction' => true,
            ]));
        }

        $this->assertSame(1, User::query()->where('email', $admin->email)->count());
        $this->assertSame('Existing Administrator', $admin->fresh()->name);
        $this->assertSame($adminHash, $admin->fresh()->password);
        $this->assertSame(1, User::query()->where('email', $doctor->email)->count());
        $this->assertSame('Existing Doctor', $doctor->fresh()->name);
        $this->assertSame($doctorHash, $doctor->fresh()->password);
    }
}
