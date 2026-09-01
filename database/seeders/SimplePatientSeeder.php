<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Patient;

class SimplePatientSeeder extends Seeder
{
    public function run(): void
    {
        echo "Iniciando seeder simple...\n";
        
        try {
            $patient = Patient::create([
                'first_name' => 'Juan',
                'last_name' => 'Pérez',
                'email' => 'juan.perez@test.com',
                'phone' => '5512345678',
                'birth_date' => '1990-01-01',
                'gender' => 'M',
                'address' => 'Calle Test 123',
                'city' => 'Ciudad de México',
                'state' => 'Ciudad de México',
                'postal_code' => '12345',
                'country' => 'México',
                'is_active' => true,
                'created_by' => 1,
            ]);
            
            $patient->patient_code = 'JP' . str_pad($patient->id, 5, '0', STR_PAD_LEFT);
            $patient->save();
            
            echo "Paciente creado exitosamente con ID: " . $patient->id . "\n";
            
        } catch (\Exception $e) {
            echo "Error: " . $e->getMessage() . "\n";
        }
    }
}


