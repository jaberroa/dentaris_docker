<?php

namespace Database\Seeders;

use App\Models\Patient;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PatientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Creando pacientes de prueba...');

        // Obtener el primer usuario para created_by
        $user = User::first();
        if (!$user) {
            $this->command->error('No hay usuarios en la base de datos. Ejecuta UserSeeder primero.');
            return;
        }

        // Crear pacientes específicos con datos realistas
        $patients = [
            [
                'patient_code' => 'PAT-001',
                'first_name' => 'María',
                'last_name' => 'González',
                'email' => 'maria.gonzalez@email.com',
                'phone' => '555-0101',
                'birth_date' => '1985-03-15',
                'gender' => 'female',
                'address' => 'Av. Reforma 123, Col. Centro',
                'city' => 'Ciudad de México',
                'state' => 'CDMX',
                'postal_code' => '06000',
                'country' => 'México',
                'medical_history' => 'Hipertensión arterial controlada',
                'dental_history' => 'Ortodoncia completada a los 16 años',
                'allergies' => 'Penicilina',
                'blood_type' => 'A+',
                'occupation' => 'Ingeniera',
                'marital_status' => 'married',
                'emergency_contact_name' => 'Carlos González',
                'emergency_contact_phone' => '555-0102',
                'emergency_contact_relationship' => 'Esposo',
                'is_active' => true,
                'created_by' => $user->id,
            ],
            [
                'patient_code' => 'PAT-002',
                'first_name' => 'Juan',
                'last_name' => 'Rodríguez',
                'email' => 'juan.rodriguez@email.com',
                'phone' => '555-0201',
                'birth_date' => '1978-07-22',
                'gender' => 'male',
                'address' => 'Calle Morelos 456, Col. Roma',
                'city' => 'Ciudad de México',
                'state' => 'CDMX',
                'postal_code' => '06700',
                'country' => 'México',
                'medical_history' => 'Diabetes mellitus tipo 2',
                'dental_history' => 'Implantes dentales en molares superiores',
                'allergies' => 'Látex',
                'blood_type' => 'O+',
                'occupation' => 'Médico',
                'marital_status' => 'married',
                'emergency_contact_name' => 'Ana Rodríguez',
                'emergency_contact_phone' => '555-0202',
                'emergency_contact_relationship' => 'Esposa',
                'is_active' => true,
                'created_by' => $user->id,
            ],
            [
                'patient_code' => 'PAT-003',
                'first_name' => 'Carmen',
                'last_name' => 'López',
                'email' => 'carmen.lopez@email.com',
                'phone' => '555-0301',
                'birth_date' => '1992-11-08',
                'gender' => 'female',
                'address' => 'Av. Insurgentes 789, Col. Condesa',
                'city' => 'Ciudad de México',
                'state' => 'CDMX',
                'postal_code' => '06140',
                'country' => 'México',
                'medical_history' => 'Ninguna condición médica conocida',
                'dental_history' => 'Historia de caries frecuentes',
                'allergies' => 'Ninguna alergia conocida',
                'blood_type' => 'B+',
                'occupation' => 'Diseñadora',
                'marital_status' => 'single',
                'emergency_contact_name' => 'Luis López',
                'emergency_contact_phone' => '555-0302',
                'emergency_contact_relationship' => 'Padre',
                'is_active' => true,
                'created_by' => $user->id,
            ],
            [
                'patient_code' => 'PAT-004',
                'first_name' => 'Roberto',
                'last_name' => 'Martínez',
                'email' => 'roberto.martinez@email.com',
                'phone' => '555-0401',
                'birth_date' => '1965-05-12',
                'gender' => 'male',
                'address' => 'Calle Hidalgo 321, Col. Del Valle',
                'city' => 'Ciudad de México',
                'state' => 'CDMX',
                'postal_code' => '03100',
                'country' => 'México',
                'medical_history' => 'Hipertensión arterial, Artritis reumatoide',
                'dental_history' => 'Prótesis parcial removible',
                'allergies' => 'Aspirina',
                'blood_type' => 'AB+',
                'occupation' => 'Abogado',
                'marital_status' => 'divorced',
                'emergency_contact_name' => 'Isabel Martínez',
                'emergency_contact_phone' => '555-0402',
                'emergency_contact_relationship' => 'Hija',
                'is_active' => true,
                'created_by' => $user->id,
            ],
            [
                'patient_code' => 'PAT-005',
                'first_name' => 'Laura',
                'last_name' => 'Hernández',
                'email' => 'laura.hernandez@email.com',
                'phone' => '555-0501',
                'birth_date' => '1988-09-30',
                'gender' => 'female',
                'address' => 'Av. Universidad 654, Col. Coyoacán',
                'city' => 'Ciudad de México',
                'state' => 'CDMX',
                'postal_code' => '04000',
                'country' => 'México',
                'medical_history' => 'Asma bronquial',
                'dental_history' => 'Endodoncias en dientes anteriores',
                'allergies' => 'Metales (níquel)',
                'blood_type' => 'A-',
                'occupation' => 'Profesora',
                'marital_status' => 'married',
                'emergency_contact_name' => 'Miguel Hernández',
                'emergency_contact_phone' => '555-0502',
                'emergency_contact_relationship' => 'Esposo',
                'is_active' => true,
                'created_by' => $user->id,
            ]
        ];

        // Crear los pacientes específicos
        foreach ($patients as $patientData) {
            Patient::create($patientData);
        }

        // Crear pacientes adicionales con datos aleatorios simples
        for ($i = 6; $i <= 100; $i++) {
            $firstName = ['Ana', 'Carlos', 'María', 'José', 'Carmen', 'Luis', 'Isabel', 'Miguel', 'Laura', 'Roberto'][array_rand(['Ana', 'Carlos', 'María', 'José', 'Carmen', 'Luis', 'Isabel', 'Miguel', 'Laura', 'Roberto'])];
            $lastName = ['González', 'Rodríguez', 'López', 'Martínez', 'Hernández', 'García', 'Pérez', 'Sánchez', 'Ramírez', 'Cruz'][array_rand(['González', 'Rodríguez', 'López', 'Martínez', 'Hernández', 'García', 'Pérez', 'Sánchez', 'Ramírez', 'Cruz'])];
            $gender = ['male', 'female'][array_rand(['male', 'female'])];
            
            Patient::create([
                'patient_code' => 'PAT-' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => strtolower($firstName . '.' . $lastName . '@email.com'),
                'phone' => '555-' . str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT),
                'birth_date' => date('Y-m-d', strtotime('-' . rand(18, 80) . ' years')),
                'gender' => $gender,
                'address' => 'Dirección ' . rand(1, 999),
                'city' => 'Ciudad de México',
                'state' => 'CDMX',
                'postal_code' => str_pad(rand(1000, 9999), 5, '0', STR_PAD_LEFT),
                'country' => 'México',
                'medical_history' => rand(0, 1) ? 'Ninguna condición médica conocida' : 'Hipertensión arterial',
                'dental_history' => 'Historia dental normal',
                'allergies' => rand(0, 1) ? 'Ninguna alergia conocida' : 'Penicilina',
                'blood_type' => ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'][array_rand(['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'])],
                'occupation' => ['Ingeniero', 'Médico', 'Abogado', 'Profesor', 'Empresario'][array_rand(['Ingeniero', 'Médico', 'Abogado', 'Profesor', 'Empresario'])],
                'marital_status' => ['single', 'married', 'divorced', 'widowed'][array_rand(['single', 'married', 'divorced', 'widowed'])],
                'emergency_contact_name' => 'Contacto de Emergencia',
                'emergency_contact_phone' => '555-' . str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT),
                'emergency_contact_relationship' => 'Familiar',
                'is_active' => true,
                'created_by' => $user->id,
            ]);
        }

        $this->command->info('✅ ' . Patient::count() . ' pacientes creados exitosamente.');
    }
}