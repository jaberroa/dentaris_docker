<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'name' => 'admin',
                'display_name' => 'Administrador',
                'description' => 'Acceso completo al sistema',
                'permissions' => [
                    'view_patients', 'manage_patients',
                    'view_appointments', 'manage_appointments',
                    'view_inventory', 'manage_inventory',
                    'view_billing', 'manage_billing',
                    'view_reports', 'manage_reports',
                    'manage_notifications',
                    'view_medical_records', 'manage_medical_records',
                    'view_staff', 'manage_staff',
                    'view_treatment_plans', 'manage_treatment_plans',
                    'view_lab_works', 'manage_lab_works',
                    'view_quotes', 'manage_quotes',
                    'view_suppliers', 'manage_suppliers',
                    'view_treatments', 'manage_treatments',
                    'view_payments', 'manage_payments',
                    'view_purchases', 'manage_purchases'
                ],
                'is_active' => true,
            ],
            [
                'name' => 'dentist',
                'display_name' => 'Odontólogo',
                'description' => 'Acceso a pacientes y tratamientos',
                'permissions' => [
                    'view_patients', 'manage_patients',
                    'view_appointments', 'manage_appointments',
                    'view_medical_records', 'manage_medical_records',
                    'view_treatment_plans', 'manage_treatment_plans',
                    'view_lab_works', 'manage_lab_works',
                    'view_treatments', 'manage_treatments',
                    'view_reports'
                ],
                'is_active' => true,
            ],
            [
                'name' => 'receptionist',
                'display_name' => 'Recepcionista',
                'description' => 'Acceso a citas y pacientes',
                'permissions' => [
                    'view_patients', 'manage_patients',
                    'view_appointments', 'manage_appointments',
                    'view_quotes', 'manage_quotes'
                ],
                'is_active' => true,
            ],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(['name' => $role['name']], $role);
        }
    }
}
