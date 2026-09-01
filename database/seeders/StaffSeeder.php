<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Staff;
use App\Models\User;
use App\Models\Role;

class StaffSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Creando personal médico...');

        // Obtener o crear roles necesarios
        $doctorRole = Role::firstOrCreate(['name' => 'doctor'], [
            'display_name' => 'Doctor',
            'description' => 'Doctor especialista',
        ]);

        $dentistRole = Role::firstOrCreate(['name' => 'dentist'], [
            'display_name' => 'Odontólogo',
            'description' => 'Odontólogo general',
        ]);

        $specialistRole = Role::firstOrCreate(['name' => 'specialist'], [
            'display_name' => 'Especialista',
            'description' => 'Especialista dental',
        ]);

        // Especialidades disponibles
        $specialties = [
            'Odontología General', 'Ortodoncia', 'Cirugía Oral y Maxilofacial', 'Periodoncia',
            'Endodoncia', 'Odontopediatría', 'Prostodoncia', 'Implantología',
            'Odontología Estética', 'Radiología Oral', 'Medicina Oral', 'Patología Oral'
        ];
        
        // Universidades
        $universities = [
            'Universidad Nacional de México', 'Universidad Autónoma de México', 'Universidad de Guadalajara',
            'Universidad de Monterrey', 'Universidad de Puebla', 'Universidad de Yucatán',
            'Universidad de Sonora', 'Universidad de Veracruz', 'Universidad de Chihuahua',
            'Universidad de Tamaulipas', 'Universidad de Sinaloa', 'Universidad de Coahuila'
        ];
        
        // Idiomas disponibles
        $languages = [
            ['es', 'en'], ['es'], ['es', 'en', 'fr'], ['es', 'en', 'pt'], ['es', 'en', 'it']
        ];
        
        // Certificaciones por especialidad
        $certifications = [
            'Odontología General' => ['Certificación en Implantología', 'Certificación en Endodoncia', 'Certificación en Periodoncia'],
            'Ortodoncia' => ['Certificación en Ortodoncia Invisible', 'Certificación en Ortodoncia Lingual', 'Certificación en Ortodoncia Estética'],
            'Cirugía Oral y Maxilofacial' => ['Certificación en Cirugía de Implantes', 'Certificación en Cirugía Estética', 'Certificación en Cirugía Reconstructiva'],
            'Periodoncia' => ['Certificación en Periodoncia Avanzada', 'Certificación en Regeneración Ósea', 'Certificación en Cirugía Periodontal'],
            'Endodoncia' => ['Certificación en Endodoncia Microscópica', 'Certificación en Retratamiento Endodóntico', 'Certificación en Endodoncia Avanzada'],
            'Odontopediatría' => ['Certificación en Odontopediatría', 'Certificación en Manejo de Ansiedad Pediátrica', 'Certificación en Sedación Pediátrica'],
            'Prostodoncia' => ['Certificación en Prostodoncia Fija', 'Certificación en Prostodoncia Removible', 'Certificación en Prostodoncia Implantosoportada'],
            'Implantología' => ['Certificación en Implantología Avanzada', 'Certificación en Regeneración Ósea', 'Certificación en Cirugía Guiada'],
            'Odontología Estética' => ['Certificación en Carillas', 'Certificación en Blanqueamiento', 'Certificación en Diseño de Sonrisa'],
            'Radiología Oral' => ['Certificación en Radiología Digital', 'Certificación en CBCT', 'Certificación en Interpretación Radiológica'],
            'Medicina Oral' => ['Certificación en Medicina Oral', 'Certificación en Patología Oral', 'Certificación en Medicina Bucal'],
            'Patología Oral' => ['Certificación en Patología Oral', 'Certificación en Citología', 'Certificación en Biopsia Oral']
        ];
        
        // Roles disponibles
        $roles = ['dentist', 'specialist', 'doctor'];
        
        // Nombres para generar datos aleatorios
        $firstNames = [
            'Carlos', 'Ana', 'Roberto', 'María', 'Luis', 'Carmen', 'José', 'Laura', 'Miguel', 'Sofia',
            'Antonio', 'Elena', 'Francisco', 'Isabel', 'Manuel', 'Patricia', 'David', 'Rosa', 'Jorge', 'Andrea',
            'Fernando', 'Monica', 'Rafael', 'Claudia', 'Alejandro', 'Verónica', 'Diego', 'Gabriela', 'Sergio', 'Valentina',
            'Eduardo', 'Natalia', 'Andrés', 'Paola', 'Sebastián', 'Daniela', 'Rodrigo', 'Carolina', 'Gonzalo', 'Camila',
            'Martín', 'Fernanda', 'Emilio', 'Alejandra', 'Ricardo', 'Mariana', 'Arturo', 'Beatriz', 'Raúl', 'Adriana'
        ];
        
        $lastNames = [
            'Mendoza', 'García', 'Silva', 'López', 'Hernández', 'Ruiz', 'González', 'Martínez', 'Pérez', 'Sánchez',
            'Ramírez', 'Cruz', 'Flores', 'Morales', 'Gutiérrez', 'Reyes', 'Jiménez', 'Álvarez', 'Mendoza', 'Vargas',
            'Castillo', 'Romero', 'Moreno', 'Herrera', 'Medina', 'Aguilar', 'Vega', 'Castro', 'Ortega', 'Rubio',
            'Molina', 'Delgado', 'Ramos', 'Herrera', 'Guerrero', 'Luna', 'Rojas', 'Campos', 'Vega', 'Peña',
            'Cortés', 'Ríos', 'Miranda', 'Espinoza', 'Contreras', 'Vásquez', 'Sandoval', 'Méndez', 'Torres', 'Villa'
        ];

        // Generar 70 registros de staff
        for ($i = 1; $i <= 70; $i++) {
            $firstName = $firstNames[array_rand($firstNames)];
            $lastName = $lastNames[array_rand($lastNames)];
            $specialty = $specialties[array_rand($specialties)];
            $university = $universities[array_rand($universities)];
            $languageSet = $languages[array_rand($languages)];
            $role = $roles[array_rand($roles)];
            
            $staffData[] = [
                'name' => 'Dr(a). ' . $firstName . ' ' . $lastName,
                'email' => strtolower($firstName . '.' . $lastName . $i . '@dentaris.com'),
                'specialty' => $specialty,
                'license_number' => strtoupper(substr($specialty, 0, 3)) . '-' . str_pad($i, 5, '0', STR_PAD_LEFT),
                'university' => $university,
                'graduation_year' => rand(2010, 2023),
                'consultation_fee' => rand(500, 2000),
                'experience_years' => rand(2, 20),
                'languages' => $languageSet,
                'certifications' => array_slice($certifications[$specialty] ?? ['Certificación Profesional'], 0, rand(1, 3)),
                'role' => $role,
            ];
        }

        foreach ($staffData as $data) {
            // Crear usuario
            $user = User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'password' => bcrypt('password'),
                    'email_verified_at' => now(),
                ]
            );

            // Asignar rol
            $role = Role::where('name', $data['role'])->first();
            if ($role && !$user->hasRole($role->name)) {
                $user->assignRole($role);
            }

            // Crear staff
            Staff::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'employee_id' => 'EMP-' . str_pad($user->id, 4, '0', STR_PAD_LEFT),
                    'specialty' => $data['specialty'],
                    'license_number' => $data['license_number'],
                    'license_expiry' => now()->addYears(3),
                    'university' => $data['university'],
                    'graduation_year' => $data['graduation_year'],
                    'bio' => 'Profesional altamente capacitado con amplia experiencia en ' . $data['specialty'] . '.',
                    'consultation_fee' => $data['consultation_fee'],
                    'experience_years' => $data['experience_years'],
                    'languages' => $data['languages'],
                    'certifications' => $data['certifications'],
                    'is_available' => true,
                    'is_active' => true,
                ]
            );
        }

        $this->command->info('✅ Personal médico creado exitosamente:');
        $this->command->info('- 70 profesionales médicos');
        $this->command->info('- Roles asignados correctamente');
        $this->command->info('- Credenciales y especialidades configuradas');
    }
}