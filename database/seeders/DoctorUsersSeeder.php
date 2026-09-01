<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Staff;
use Illuminate\Support\Facades\Hash;

class DoctorUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $doctors = [
            [
                'name' => 'Dr. Carlos Mendoza',
                'email' => 'carlos.mendoza@dentaris.com',
                'password' => Hash::make('password123'),
                'phone' => '+57 300 123 4567',
                'address' => 'Calle 123 #45-67, Bogotá',
                'birth_date' => '1980-05-15',
                'gender' => 'male',
                'specialty' => 'Endodoncia',
                'license_number' => 'END123456',
                'is_active' => true,
                'staff' => [
                    'employee_id' => 'EMP' . uniqid(),
                    'specialty' => 'Endodoncia',
                    'license_number' => 'END123456',
                    'license_expiry' => '2025-12-31',
                    'university' => 'Universidad Javeriana',
                    'graduation_year' => 2005,
                    'bio' => 'Especialista en Endodoncia con más de 15 años de experiencia',
                    'consultation_fee' => 80000,
                    'experience_years' => 15,
                    'languages' => 'Español, Inglés',
                    'certifications' => 'Certificación en Endodoncia Avanzada',
                    'is_available' => true,
                    'is_active' => true
                ]
            ],
            [
                'name' => 'Dra. Ana Patricia Silva',
                'email' => 'ana.silva@dentaris.com',
                'password' => Hash::make('password123'),
                'phone' => '+57 300 234 5678',
                'address' => 'Carrera 45 #78-90, Medellín',
                'birth_date' => '1985-08-22',
                'gender' => 'female',
                'specialty' => 'Periodoncia',
                'license_number' => 'PER234567',
                'is_active' => true,
                'staff' => [
                    'employee_id' => 'EMP' . uniqid(),
                    'specialty' => 'Periodoncia',
                    'license_number' => 'PER234567',
                    'license_expiry' => '2025-12-31',
                    'university' => 'Universidad CES',
                    'graduation_year' => 2010,
                    'bio' => 'Especialista en Periodoncia e Implantes',
                    'consultation_fee' => 90000,
                    'experience_years' => 12,
                    'languages' => 'Español, Inglés, Francés',
                    'certifications' => 'Certificación en Implantes Dentales',
                    'is_available' => true,
                    'is_active' => true
                ]
            ],
            [
                'name' => 'Dr. Roberto Herrera',
                'email' => 'roberto.herrera@dentaris.com',
                'password' => Hash::make('password123'),
                'phone' => '+57 300 345 6789',
                'address' => 'Calle 80 #12-34, Cali',
                'birth_date' => '1978-12-10',
                'gender' => 'male',
                'specialty' => 'Cirugía Oral',
                'license_number' => 'CIR345678',
                'is_active' => true,
                'staff' => [
                    'employee_id' => 'EMP' . uniqid(),
                    'specialty' => 'Cirugía Oral',
                    'license_number' => 'CIR345678',
                    'license_expiry' => '2025-12-31',
                    'university' => 'Universidad del Valle',
                    'graduation_year' => 2003,
                    'bio' => 'Cirujano Oral y Maxilofacial con amplia experiencia',
                    'consultation_fee' => 120000,
                    'experience_years' => 18,
                    'languages' => 'Español, Inglés',
                    'certifications' => 'Certificación en Cirugía Maxilofacial',
                    'is_available' => true,
                    'is_active' => true
                ]
            ],
            [
                'name' => 'Dra. Laura Cristina Vega',
                'email' => 'laura.vega@dentaris.com',
                'password' => Hash::make('password123'),
                'phone' => '+57 300 456 7890',
                'address' => 'Carrera 15 #90-12, Barranquilla',
                'birth_date' => '1987-03-18',
                'gender' => 'female',
                'specialty' => 'Prótesis Dental',
                'license_number' => 'PRO456789',
                'is_active' => true,
                'staff' => [
                    'employee_id' => 'EMP' . uniqid(),
                    'specialty' => 'Prótesis Dental',
                    'license_number' => 'PRO456789',
                    'license_expiry' => '2025-12-31',
                    'university' => 'Universidad del Norte',
                    'graduation_year' => 2012,
                    'bio' => 'Especialista en Prótesis y Estética Dental',
                    'consultation_fee' => 75000,
                    'experience_years' => 10,
                    'languages' => 'Español, Inglés',
                    'certifications' => 'Certificación en Estética Dental',
                    'is_available' => true,
                    'is_active' => true
                ]
            ],
            [
                'name' => 'Dr. Miguel Ángel Torres',
                'email' => 'miguel.torres@dentaris.com',
                'password' => Hash::make('password123'),
                'phone' => '+57 300 567 8901',
                'address' => 'Calle 100 #23-45, Bucaramanga',
                'birth_date' => '1983-07-25',
                'gender' => 'male',
                'specialty' => 'Odontopediatría',
                'license_number' => 'PED567890',
                'is_active' => true,
                'staff' => [
                    'employee_id' => 'EMP' . uniqid(),
                    'specialty' => 'Odontopediatría',
                    'license_number' => 'PED567890',
                    'license_expiry' => '2025-12-31',
                    'university' => 'Universidad Industrial de Santander',
                    'graduation_year' => 2008,
                    'bio' => 'Especialista en Odontología Pediátrica',
                    'consultation_fee' => 60000,
                    'experience_years' => 14,
                    'languages' => 'Español, Inglés',
                    'certifications' => 'Certificación en Odontología Pediátrica',
                    'is_available' => true,
                    'is_active' => true
                ]
            ]
        ];

        foreach ($doctors as $doctorData) {
            $staffData = $doctorData['staff'];
            unset($doctorData['staff']);

            // Crear o actualizar usuario
            $user = User::updateOrCreate(
                ['email' => $doctorData['email']],
                $doctorData
            );

            // Crear o actualizar staff
            $staff = Staff::updateOrCreate(
                ['user_id' => $user->id],
                array_merge($staffData, ['user_id' => $user->id])
            );

            $this->command->info("Doctor creado: {$user->name} - {$user->email} - Staff ID: {$staff->id}");
        }

        $this->command->info('✅ 5 doctores creados exitosamente');
    }
}
