<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Patient;
use App\Models\User;
use Faker\Factory as Faker;

class PatientTestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('es_ES'); // Usar datos en español
        
        // Obtener usuarios existentes para asignar como creadores
        $users = User::all();
        if ($users->isEmpty()) {
            $this->command->error('No hay usuarios en la base de datos. Ejecuta primero UserSeeder.');
            return;
        }

        $this->command->info('Creando 100 pacientes de prueba con datos realistas...');

        for ($i = 0; $i < 100; $i++) {
            try {
            $firstName = $faker->firstName();
            $lastName = $faker->lastName();
            
            // Crear email sin caracteres especiales
            $email = strtolower($firstName . '.' . $lastName . '@' . $faker->freeEmailDomain());
            $email = preg_replace('/[^a-zA-Z0-9@.]/', '', $email);
            
            // Generar teléfono realista
            $phone = $faker->numerify('9#######');
            
            // Fecha de nacimiento realista (entre 18 y 80 años)
            $birthDate = $faker->dateTimeBetween('-80 years', '-18 years');
            
            // Dirección realista
            $address = $faker->streetAddress();
            $city = $faker->city();
            $state = $faker->state();
            
            // Datos médicos realistas
            $allergies = $faker->optional(0.3)->randomElement([
                'Penicilina', 'Aspirina', 'Látex', 'Mariscos', 'Frutos secos', 
                'Polen', 'Polvo', 'Ninguna alergia conocida'
            ]);
            
            $medicalHistory = $faker->optional(0.4)->randomElement([
                'Diabetes', 'Hipertensión', 'Asma', 'Problemas cardíacos', 
                'Artritis', 'Ninguna condición médica'
            ]);
            
            $dentalHistory = $faker->optional(0.3)->randomElement([
                'Ortodoncia previa', 'Implantes dentales', 'Endodoncia', 
                'Periodoncia', 'Ningún tratamiento dental previo'
            ]);
            
            $medications = $faker->optional(0.3)->randomElement([
                'Metformina', 'Losartán', 'Omeprazol', 'Atorvastatina', 
                'Ninguna medicación actual'
            ]);
            
            $familyHistory = $faker->optional(0.4)->randomElement([
                'Diabetes familiar', 'Hipertensión familiar', 'Problemas cardíacos familiares',
                'Cáncer familiar', 'Ningún antecedente familiar relevante'
            ]);
            
            $socialHistory = $faker->optional(0.3)->randomElement([
                'No fuma', 'Ex fumador', 'Fumador ocasional', 'Fumador activo',
                'Consumo social de alcohol', 'No consume alcohol'
            ]);
            
            $bloodType = $faker->randomElement(['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-']);
            
            // Contacto de emergencia
            $emergencyName = $faker->name();
            $emergencyPhone = $faker->numerify('9#######');
            $emergencyRelation = $faker->randomElement([
                'Cónyuge', 'Padre', 'Madre', 'Hijo', 'Hija', 'Hermano', 'Hermana', 'Amigo'
            ]);
            $emergencyAddress = $faker->address();
            
            // Datos de trabajo
            $occupation = $faker->randomElement([
                'Ingeniero', 'Médico', 'Abogado', 'Profesor', 'Comerciante', 
                'Estudiante', 'Empleado', 'Empresario', 'Arquitecto', 'Contador'
            ]);
            
            $maritalStatus = $faker->randomElement([
                'Soltero', 'Casado', 'Divorciado', 'Viudo', 'Unión libre'
            ]);
            
            // Datos de consentimiento
            $consentDataProcessing = $faker->boolean(90); // 90% acepta
            $consentMarketing = $faker->boolean(60); // 60% acepta marketing
            
            // Notas médicas
            $notes = $faker->optional(0.4)->sentence(15);
            
            // Estado del paciente
            $isActive = $faker->boolean(95); // 95% activo
            
            // Crear el paciente
            $patient = Patient::create([
                'patient_code' => Patient::generateUniquePatientCode($firstName, $lastName, null),
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $email,
                'phone' => $phone,
                'phone_secondary' => $faker->optional(0.3)->numerify('9#######'),
                'birth_date' => $birthDate,
                'gender' => $faker->randomElement(['male', 'female', 'other']),
                'address' => $address,
                'city' => $city,
                'state' => $state,
                'postal_code' => $faker->postcode(),
                'country' => 'Chile',
                'medical_history' => $medicalHistory,
                'dental_history' => $dentalHistory,
                'allergies' => $allergies,
                'medications' => $medications,
                'family_history' => $familyHistory,
                'social_history' => $socialHistory,
                'blood_type' => $bloodType,
                'occupation' => $occupation,
                'marital_status' => $maritalStatus,
                'emergency_contact_name' => $emergencyName,
                'emergency_contact_phone' => $emergencyPhone,
                'emergency_contact_relationship' => $emergencyRelation,
                'emergency_contact_address' => $emergencyAddress,
                'notes' => $notes,
                'preferences' => $faker->optional(0.2)->sentence(10),
                'consent_data_processing' => $consentDataProcessing,
                'consent_marketing' => $consentMarketing,
                'is_active' => $isActive,
                'created_by' => $users->random()->id,
            ]);
            
            // Mostrar progreso cada 10 pacientes
            if (($i + 1) % 10 == 0) {
                $this->command->info("Creados " . ($i + 1) . " pacientes...");
            }
            } catch (\Exception $e) {
                $this->command->error("Error creando paciente " . ($i + 1) . ": " . $e->getMessage());
                continue;
            }
        }
        
        $this->command->info('✅ 100 pacientes de prueba creados exitosamente!');
        $this->command->info('📊 Datos incluidos: nombres realistas, emails válidos, teléfonos, direcciones, datos médicos, contactos de emergencia, etc.');
    }
}