<?php

namespace Database\Seeders;

use App\Models\Inventory;
use App\Models\Patient;
use App\Models\Product;
use App\Models\Role;
use App\Models\Staff;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        // Obtener roles existentes
        $adminRole = Role::where('name', 'admin')->first();
        $dentistRole = Role::where('name', 'dentist')->first();

        // Crear usuarios solo si faltan; nunca sobrescribir identidades existentes.
        $admin = User::firstOrCreate(
            ['email' => 'admin@dentaris.com'],
            [
                'name' => 'Dr. Juan Pérez',
                'email' => 'admin@dentaris.com',
                'password' => Hash::make(Str::password(32)),
                'phone' => '+1234567890',
                'gender' => 'male',
                'specialty' => 'Odontología General',
                'license_number' => 'OD-001234',
                'is_active' => true,
            ]
        );
        $admin->roles()->syncWithoutDetaching([$adminRole->id]);

        $dentist = User::firstOrCreate(
            ['email' => 'dentist@dentaris.com'],
            [
                'name' => 'Dra. María González',
                'email' => 'dentist@dentaris.com',
                'password' => Hash::make(Str::password(32)),
                'phone' => '+1234567891',
                'gender' => 'female',
                'specialty' => 'Endodoncia',
                'license_number' => 'OD-005678',
                'is_active' => true,
            ]
        );
        $dentist->roles()->syncWithoutDetaching([$dentistRole->id]);

        // Crear staff
        $adminStaff = Staff::updateOrCreate(
            ['user_id' => $admin->id],
            [
                'employee_id' => 'EMP-001',
                'specialty' => 'Odontología General',
                'license_number' => 'OD-001234',
                'consultation_fee' => 150.00,
                'experience_years' => 10,
                'is_available' => true,
                'is_active' => true,
            ]
        );

        // Crear pacientes
        $patient1 = Patient::updateOrCreate(
            ['patient_code' => 'PAT-001'],
            [
                'first_name' => 'Roberto',
                'last_name' => 'Martínez',
                'email' => 'roberto.martinez@email.com',
                'phone' => '+1234567801',
                'birth_date' => '1985-04-12',
                'gender' => 'male',
                'is_active' => true,
                'created_by' => $admin->id,
            ]
        );

        // Crear proveedor
        $supplier1 = Supplier::updateOrCreate(
            ['supplier_code' => 'SUP-001'],
            [
                'company_name' => 'Dental Supplies Corp',
                'contact_name' => 'Miguel Torres',
                'email' => 'miguel.torres@dentalsupplies.com',
                'phone' => '+1234567901',
                'payment_terms' => 'net_30',
                'credit_limit' => 50000.00,
                'is_active' => true,
                'created_by' => $admin->id,
            ]
        );

        // Crear producto
        $product1 = Product::updateOrCreate(
            ['product_code' => 'PROD-001'],
            [
                'name' => 'Amalgama Dental',
                'description' => 'Amalgama de plata para restauraciones',
                'category' => 'Materiales Restaurativos',
                'unit_of_measure' => 'piezas',
                'cost_price' => 45.00,
                'selling_price' => 65.00,
                'minimum_stock' => 10,
                'maximum_stock' => 100,
                'is_active' => true,
                'primary_supplier_id' => $supplier1->id,
                'created_by' => $admin->id,
            ]
        );

        // Crear inventario
        Inventory::updateOrCreate(
            ['product_id' => $product1->id],
            [
                'current_stock' => 50,
                'available_stock' => 50,
                'average_cost' => 45.00,
                'last_restocked' => now()->subDays(10),
                'location' => 'Almacén Principal',
            ]
        );
    }
}
