<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Patient;
use App\Models\User;

class PatientTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Creando 100 pacientes de prueba...');

        // Nombres y apellidos mexicanos realistas
        $firstNames = [
            'Alejandro', 'Ana', 'Antonio', 'Beatriz', 'Carlos', 'Carmen', 'Diego', 'Elena', 'Fernando', 'Gabriela',
            'Héctor', 'Isabel', 'Javier', 'Laura', 'Luis', 'María', 'Miguel', 'Natalia', 'Oscar', 'Patricia',
            'Ricardo', 'Rosa', 'Sergio', 'Sofia', 'Tomás', 'Valentina', 'Víctor', 'Ximena', 'Yolanda', 'Zacarias',
            'Adriana', 'Bruno', 'Cecilia', 'Daniel', 'Estela', 'Francisco', 'Gloria', 'Hugo', 'Irene', 'José',
            'Karla', 'Leonardo', 'Mónica', 'Nicolás', 'Olivia', 'Pedro', 'Quetzal', 'Rebeca', 'Samuel', 'Teresa',
            'Ulises', 'Verónica', 'Wilfredo', 'Yolanda', 'Zulema', 'Abraham', 'Blanca', 'Cristian', 'Diana', 'Edgar',
            'Fabiola', 'Gerardo', 'Hilda', 'Iván', 'Jazmín', 'Kevin', 'Leticia', 'Manuel', 'Nora', 'Octavio',
            'Paola', 'Ramón', 'Silvia', 'Tadeo', 'Úrsula', 'Vanesa', 'Walter', 'Xochitl', 'Yazmín', 'Zoe'
        ];

        $lastNames = [
            'García', 'López', 'Martínez', 'González', 'Pérez', 'Sánchez', 'Ramírez', 'Cruz', 'Flores', 'Morales',
            'Hernández', 'Jiménez', 'Álvarez', 'Ruiz', 'Torres', 'Díaz', 'Vargas', 'Castro', 'Romero', 'Sosa',
            'Mendoza', 'Gutiérrez', 'Ortiz', 'Silva', 'Reyes', 'Guerrero', 'Luna', 'Rojas', 'Campos', 'Vega',
            'Peña', 'Cortés', 'Ríos', 'Miranda', 'Espinoza', 'Contreras', 'Vásquez', 'Sandoval', 'Méndez', 'Torres',
            'Herrera', 'Medina', 'Aguilar', 'Vega', 'Castro', 'Ortega', 'Rubio', 'Molina', 'Delgado', 'Ramos',
            'Herrera', 'Guerrero', 'Luna', 'Rojas', 'Campos', 'Vega', 'Peña', 'Cortés', 'Ríos', 'Miranda',
            'Espinoza', 'Contreras', 'Vásquez', 'Sandoval', 'Méndez', 'Torres', 'Herrera', 'Medina', 'Aguilar', 'Vega',
            'Castro', 'Ortega', 'Rubio', 'Molina', 'Delgado', 'Ramos', 'Herrera', 'Guerrero', 'Luna', 'Rojas'
        ];

        // Ocupaciones realistas
        $occupations = [
            'Ingeniero', 'Médico', 'Abogado', 'Contador', 'Profesor', 'Arquitecto', 'Enfermero', 'Psicólogo',
            'Veterinario', 'Chef', 'Mecánico', 'Electricista', 'Carpintero', 'Pintor', 'Soldador', 'Programador',
            'Diseñador', 'Músico', 'Artista', 'Escritor', 'Periodista', 'Fotógrafo', 'Actor', 'Bailarín',
            'Atleta', 'Entrenador', 'Nutriólogo', 'Dentista', 'Farmacéutico', 'Químico', 'Biólogo', 'Geólogo',
            'Astrónomo', 'Físico', 'Matemático', 'Economista', 'Sociólogo', 'Antropólogo', 'Historiador', 'Filósofo',
            'Comerciante', 'Empresario', 'Gerente', 'Supervisor', 'Coordinador', 'Asistente', 'Secretario', 'Recepcionista',
            'Vendedor', 'Cajero', 'Mesero', 'Bartender', 'Conductor', 'Piloto', 'Marinero', 'Agricultor', 'Ganadero',
            'Pescador', 'Minero', 'Constructor', 'Albañil', 'Plomero', 'Técnico', 'Operador', 'Mantenimiento',
            'Seguridad', 'Policía', 'Bombero', 'Militar', 'Diplomático', 'Político', 'Activista', 'Voluntario',
            'Estudiante', 'Investigador', 'Consultor', 'Freelancer', 'Emprendedor', 'Inversionista', 'Banquero',
            'Corredor', 'Agente', 'Representante', 'Ejecutivo', 'Director', 'Presidente', 'CEO', 'Fundador',
            'Co-fundador', 'Socio', 'Accionista', 'Inversionista', 'Mentor', 'Coach', 'Consultor', 'Asesor',
            'Especialista', 'Experto', 'Investigador', 'Científico', 'Tecnólogo', 'Innovador', 'Inventor'
        ];

        // Ciudades mexicanas realistas
        $cities = [
            'Ciudad de México', 'Guadalajara', 'Monterrey', 'Puebla', 'Tijuana', 'León', 'Juárez', 'Torreón',
            'Querétaro', 'San Luis Potosí', 'Mérida', 'Mexicali', 'Aguascalientes', 'Acapulco', 'Cuernavaca',
            'Saltillo', 'Hermosillo', 'Morelia', 'Culiacán', 'Chihuahua', 'Villahermosa', 'Cancún', 'Toluca',
            'Reynosa', 'Tampico', 'Irapuato', 'Tuxtla Gutiérrez', 'Durango', 'Matamoros', 'Colima', 'Xalapa',
            'Mazatlán', 'Nuevo Laredo', 'Oaxaca', 'Campeche', 'Pachuca', 'Coatzacoalcos', 'Uruapan', 'Veracruz',
            'Córdoba', 'Poza Rica', 'Tlaxcala', 'Ciudad Victoria', 'Ensenada', 'La Paz', 'Chetumal', 'Chilpancingo',
            'Zacatecas', 'Orizaba', 'Tulancingo', 'Celaya', 'San Juan del Río', 'Irapuato', 'Tepatitlán', 'Salamanca'
        ];

        // Estados mexicanos
        $states = [
            'Aguascalientes', 'Baja California', 'Baja California Sur', 'Campeche', 'Chiapas', 'Chihuahua',
            'Ciudad de México', 'Coahuila', 'Colima', 'Durango', 'Estado de México', 'Guanajuato', 'Guerrero',
            'Hidalgo', 'Jalisco', 'Michoacán', 'Morelos', 'Nayarit', 'Nuevo León', 'Oaxaca', 'Puebla', 'Querétaro',
            'Quintana Roo', 'San Luis Potosí', 'Sinaloa', 'Sonora', 'Tabasco', 'Tamaulipas', 'Tlaxcala',
            'Veracruz', 'Yucatán', 'Zacatecas'
        ];

        // Tipos de sangre
        $bloodTypes = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];

        // Géneros
        $genders = ['M', 'F'];

        // Estados civiles
        $maritalStatuses = ['Soltero', 'Casado', 'Divorciado', 'Viudo', 'Unión libre'];

        // Niveles de educación
        $educationLevels = [
            'Primaria', 'Secundaria', 'Preparatoria', 'Técnico', 'Licenciatura', 'Maestría', 'Doctorado'
        ];

        // Antecedentes médicos comunes
        $medicalConditions = [
            'Diabetes', 'Hipertensión', 'Asma', 'Alergias', 'Artritis', 'Migraña', 'Depresión', 'Ansiedad',
            'Colesterol alto', 'Gastritis', 'Reflujo', 'Insomnio', 'Obesidad', 'Anemia', 'Tiroides', 'Ninguno'
        ];

        // Medicamentos comunes
        $medications = [
            'Metformina', 'Losartán', 'Omeprazol', 'Paracetamol', 'Ibuprofeno', 'Aspirina', 'Loratadina',
            'Cetirizina', 'Ranitidina', 'Simvastatina', 'Amlodipino', 'Enalapril', 'Atorvastatina', 'Ninguno'
        ];

        // Contactos de emergencia (relaciones)
        $emergencyRelations = [
            'Padre', 'Madre', 'Hijo', 'Hija', 'Hermano', 'Hermana', 'Esposo', 'Esposa', 'Tío', 'Tía',
            'Primo', 'Prima', 'Abuelo', 'Abuela', 'Suegro', 'Suegra', 'Cuñado', 'Cuñada', 'Amigo', 'Amiga'
        ];

        // Generar 100 pacientes
        $this->command->info('Iniciando creación de pacientes...');
        
        for ($i = 1; $i <= 100; $i++) {
            $firstName = $firstNames[array_rand($firstNames)];
            $lastName = $lastNames[array_rand($lastNames)];
            $gender = $genders[array_rand($genders)];
            $city = $cities[array_rand($cities)];
            $state = $states[array_rand($states)];
            
            // Generar email sin caracteres especiales
            $emailBase = strtolower($firstName . $lastName . $i);
            $emailBase = str_replace(['á', 'é', 'í', 'ó', 'ú', 'ñ'], ['a', 'e', 'i', 'o', 'u', 'n'], $emailBase);
            $email = $emailBase . '@email.com';
            
            // Generar teléfono mexicano
            $phone = '55' . rand(1000, 9999) . rand(1000, 9999);
            
            // Generar fecha de nacimiento (entre 18 y 80 años)
            $birthDate = now()->subYears(rand(18, 80))->subDays(rand(0, 365));
            
            // Generar dirección
            $streetNumber = rand(1, 999);
            $street = ['Calle', 'Avenida', 'Boulevard', 'Privada', 'Cerrada'][array_rand(['Calle', 'Avenida', 'Boulevard', 'Privada', 'Cerrada'])];
            $streetName = ['Reforma', 'Insurgentes', 'Juárez', 'Hidalgo', 'Morelos', 'Zaragoza', 'Independencia'][array_rand(['Reforma', 'Insurgentes', 'Juárez', 'Hidalgo', 'Morelos', 'Zaragoza', 'Independencia'])];
            $address = $street . ' ' . $streetName . ' #' . $streetNumber . ', Col. Centro, ' . $city . ', ' . $state;
            
            // Generar código postal
            $zipCode = rand(10000, 99999);
            
            // Datos del paciente
            $patientData = [
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $email,
                'phone' => $phone,
                'birth_date' => $birthDate->format('Y-m-d'),
                'gender' => $gender,
                'address' => $address,
                'city' => $city,
                'state' => $state,
                'postal_code' => $zipCode,
                'country' => 'México',
                'occupation' => $occupations[array_rand($occupations)],
                'marital_status' => $maritalStatuses[array_rand($maritalStatuses)],
                'blood_type' => $bloodTypes[array_rand($bloodTypes)],
                'emergency_contact_name' => $firstNames[array_rand($firstNames)] . ' ' . $lastNames[array_rand($lastNames)],
                'emergency_contact_phone' => '55' . rand(1000, 9999) . rand(1000, 9999),
                'emergency_contact_relationship' => $emergencyRelations[array_rand($emergencyRelations)],
                'emergency_contact_address' => $address,
                'medical_history' => $medicalConditions[array_rand($medicalConditions)],
                'dental_history' => 'Sin antecedentes dentales relevantes',
                'family_history' => 'Sin antecedentes familiares relevantes',
                'social_history' => 'No fumador, consumo ocasional de alcohol',
                'medications' => $medications[array_rand($medications)],
                'allergies' => rand(0, 1) ? 'Ninguna' : 'Penicilina, Polen, Mariscos',
                'notes' => 'Paciente de prueba generado automáticamente. Datos realistas para testing.',
                'preferences' => json_encode(['idioma' => 'español', 'notificaciones' => 'email']),
                'consent_marketing' => rand(0, 1),
                'consent_data_processing' => true,
                'is_active' => rand(0, 1) ? true : false,
                'created_by' => 1, // Usuario admin por defecto
                'created_at' => now()->subDays(rand(0, 365)),
                'updated_at' => now()->subDays(rand(0, 30)),
            ];

            try {
                // Crear paciente
                $patient = Patient::create($patientData);
                
                // Generar código de paciente único
                $patient->patient_code = Patient::generateUniquePatientCode($firstName, $lastName, $patient->id);
                $patient->save();
                
                if ($i % 10 == 0) {
                    $this->command->info("Creados $i pacientes...");
                }
            } catch (\Exception $e) {
                $this->command->error("Error creando paciente $i: " . $e->getMessage());
                continue;
            }
        }

        $this->command->info('✅ 100 pacientes de prueba creados exitosamente');
        $this->command->info('- Nombres y apellidos mexicanos realistas');
        $this->command->info('- Emails sin caracteres especiales');
        $this->command->info('- Teléfonos mexicanos válidos');
        $this->command->info('- Direcciones y ciudades mexicanas');
        $this->command->info('- Datos médicos realistas');
        $this->command->info('- Códigos de paciente únicos generados');
    }
}