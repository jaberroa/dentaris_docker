<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear roles si no existen
        $adminRole = Role::firstOrCreate(['name' => 'admin'], [
            'display_name' => 'Administrador',
            'description' => 'Acceso completo al sistema'
        ]);

        $dentistRole = Role::firstOrCreate(['name' => 'dentist'], [
            'display_name' => 'Odontólogo',
            'description' => 'Acceso a pacientes y tratamientos'
        ]);

        $receptionistRole = Role::firstOrCreate(['name' => 'receptionist'], [
            'display_name' => 'Recepcionista',
            'description' => 'Acceso a citas y pacientes'
        ]);

        // Crear usuario administrador
        $admin = User::firstOrCreate(['email' => 'admin@dentaris.com'], [
            'name' => 'Administrador',
            'email' => 'admin@dentaris.com',
            'password' => Hash::make('password'),
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        // Asignar rol de administrador
        $admin->roles()->syncWithoutDetaching([$adminRole->id]);

        // Crear usuario odontólogo
        $dentist = User::firstOrCreate(['email' => 'dentist@dentaris.com'], [
            'name' => 'Dr. Juan Pérez',
            'email' => 'dentist@dentaris.com',
            'password' => Hash::make('password'),
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        // Asignar rol de odontólogo
        $dentist->roles()->syncWithoutDetaching([$dentistRole->id]);

        // Crear usuario recepcionista
        $receptionist = User::firstOrCreate(['email' => 'reception@dentaris.com'], [
            'name' => 'María González',
            'email' => 'reception@dentaris.com',
            'password' => Hash::make('password'),
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        // Asignar rol de recepcionista
        $receptionist->roles()->syncWithoutDetaching([$receptionistRole->id]);

        $this->command->info('Usuarios creados exitosamente:');
        $this->command->info('- admin@dentaris.com / password (Administrador)');
        $this->command->info('- dentist@dentaris.com / password (Odontólogo)');
        $this->command->info('- reception@dentaris.com / password (Recepcionista)');
    }
}
