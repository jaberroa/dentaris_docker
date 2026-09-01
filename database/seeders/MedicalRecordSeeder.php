<?php

namespace Database\Seeders;

use App\Models\MedicalRecord;
use App\Models\Patient;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Database\Seeder;

class MedicalRecordSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Creando historias clínicas de prueba...');

        // Obtener pacientes y staff existentes
        $patients = Patient::where('is_active', true)->get();
        $staff = Staff::with('user')->get();
        $users = User::all();

        if ($patients->isEmpty()) {
            $this->command->warn('No hay pacientes activos. Ejecuta primero PatientSeeder.');
            return;
        }

        if ($staff->isEmpty()) {
            $this->command->warn('No hay staff registrado. Ejecuta primero StaffSeeder.');
            return;
        }

        if ($users->isEmpty()) {
            $this->command->warn('No hay usuarios. Ejecuta primero UserSeeder.');
            return;
        }

        // Crear historias clínicas para diferentes tipos de registros
        $totalRecords = 0;

        // 1. Consultas generales (40%)
        $consultationCount = (int) ($patients->count() * 0.4);
        for ($i = 0; $i < $consultationCount; $i++) {
            MedicalRecord::factory()
                ->consultation()
                ->create([
                    'patient_id' => $patients->random()->id,
                    'staff_id' => $staff->random()->id,
                    'created_by' => $users->random()->id,
                ]);
            $totalRecords++;
        }

        // 2. Tratamientos (30%)
        $treatmentCount = (int) ($patients->count() * 0.3);
        for ($i = 0; $i < $treatmentCount; $i++) {
            MedicalRecord::factory()
                ->treatment()
                ->create([
                    'patient_id' => $patients->random()->id,
                    'staff_id' => $staff->random()->id,
                    'created_by' => $users->random()->id,
                ]);
            $totalRecords++;
        }

        // 3. Seguimientos (20%)
        $followUpCount = (int) ($patients->count() * 0.2);
        for ($i = 0; $i < $followUpCount; $i++) {
            MedicalRecord::factory()
                ->followUp()
                ->create([
                    'patient_id' => $patients->random()->id,
                    'staff_id' => $staff->random()->id,
                    'created_by' => $users->random()->id,
                ]);
            $totalRecords++;
        }

        // 4. Urgencias (10%)
        $emergencyCount = (int) ($patients->count() * 0.1);
        for ($i = 0; $i < $emergencyCount; $i++) {
            MedicalRecord::factory()
                ->emergency()
                ->create([
                    'patient_id' => $patients->random()->id,
                    'staff_id' => $staff->random()->id,
                    'created_by' => $users->random()->id,
                ]);
            $totalRecords++;
        }

        // 5. Algunos registros confidenciales (5%)
        $confidentialCount = (int) ($totalRecords * 0.05);
        MedicalRecord::inRandomOrder()->limit($confidentialCount)->update(['is_confidential' => true]);

        // 6. Crear historias clínicas específicas para pacientes especiales
        $this->createSpecialMedicalRecords($patients, $staff, $users);

        $totalRecords = MedicalRecord::count();
        $consultations = MedicalRecord::where('record_type', 'consulta')->count();
        $treatments = MedicalRecord::where('record_type', 'tratamiento')->count();
        $followUps = MedicalRecord::where('record_type', 'seguimiento')->count();
        $emergencies = MedicalRecord::where('record_type', 'urgencia')->count();
        $confidential = MedicalRecord::where('is_confidential', true)->count();

        $this->command->info("✅ Se crearon {$totalRecords} historias clínicas:");
        $this->command->info("   - Consultas: {$consultations}");
        $this->command->info("   - Tratamientos: {$treatments}");
        $this->command->info("   - Seguimientos: {$followUps}");
        $this->command->info("   - Urgencias: {$emergencies}");
        $this->command->info("   - Confidenciales: {$confidential}");
    }

    /**
     * Crear historias clínicas específicas para casos especiales
     */
    private function createSpecialMedicalRecords($patients, $staff, $users): void
    {
        // Buscar pacientes específicos por nombre
        $maria = $patients->where('first_name', 'María')->where('last_name', 'González Pérez')->first();
        $roberto = $patients->where('first_name', 'Roberto')->where('last_name', 'Martínez López')->first();
        $carmen = $patients->where('first_name', 'Carmen')->where('last_name', 'Rodríguez Silva')->first();

        if ($maria) {
            // Historia clínica para María González
            MedicalRecord::create([
                'patient_id' => $maria->id,
                'staff_id' => $staff->random()->id,
                'record_type' => 'consulta',
                'chief_complaint' => 'Dolor en molar superior derecho desde hace 3 días',
                'present_illness' => 'El dolor comenzó de forma súbita, es constante y se intensifica con el frío. No hay fiebre ni inflamación visible.',
                'medical_history' => 'Hipertensión arterial controlada con Losartán 50mg',
                'dental_history' => 'Ortodoncia en la adolescencia, limpieza dental regular cada 6 meses',
                'family_history' => 'Madre con diabetes tipo 2',
                'social_history' => 'No fuma, consume alcohol ocasionalmente',
                'clinical_examination' => 'Paciente en buen estado general. Examen oral: encías ligeramente inflamadas en cuadrante superior derecho, dolor a la percusión en diente 16',
                'vital_signs' => 'TA: 125/80 mmHg, FC: 72 lpm, Temp: 36.5°C',
                'oral_examination' => 'Dentición completa, higiene oral regular, presencia de placa en molares superiores',
                'diagnostic_impression' => 'Caries dental en diente 16 con posible pulpitis reversible',
                'treatment_plan' => "1. Limpieza dental profesional\n2. Restauración con resina en diente 16\n3. Control en 1 mes",
                'recommendations' => 'Mantener higiene oral adecuada, cepillado 3 veces al día, uso de hilo dental',
                'notes' => 'Paciente preferencial, siempre puntual. Alergia a penicilina documentada.',
                'is_confidential' => false,
                'created_by' => $users->random()->id,
            ]);
        }

        if ($roberto) {
            // Historia clínica para Roberto Martínez
            MedicalRecord::create([
                'patient_id' => $roberto->id,
                'staff_id' => $staff->random()->id,
                'record_type' => 'tratamiento',
                'chief_complaint' => 'Sangrado de encías al cepillarse desde hace una semana',
                'present_illness' => 'El sangrado se presenta especialmente en la mañana, las encías se ven inflamadas y rojas',
                'medical_history' => 'Diabetes tipo 2 bien controlada con Metformina 500mg e Insulina',
                'dental_history' => 'Extracción de muelas del juicio hace 5 años, limpieza dental irregular',
                'family_history' => 'Padre con enfermedad periodontal',
                'social_history' => 'Fumador ocasional, consume alcohol moderadamente',
                'clinical_examination' => 'Estado general estable. Examen oral: presencia de placa bacteriana, encías con signos de gingivitis generalizada',
                'vital_signs' => 'TA: 130/85 mmHg, FC: 75 lpm, Temp: 36.8°C',
                'oral_examination' => 'Dentición completa, restauraciones en buen estado, cálculo dental presente',
                'diagnostic_impression' => 'Gingivitis crónica, requiere limpieza dental profesional',
                'treatment_plan' => "1. Detartraje y alisado radicular\n2. Instrucciones de higiene oral\n3. Control en 3 meses",
                'recommendations' => 'Uso de hilo dental diario, enjuague bucal con flúor, control de diabetes',
                'notes' => 'Requiere citas matutinas por trabajo. Alergia a látex documentada.',
                'is_confidential' => false,
                'created_by' => $users->random()->id,
            ]);
        }

        if ($carmen) {
            // Historia clínica para Carmen Rodríguez
            MedicalRecord::create([
                'patient_id' => $carmen->id,
                'staff_id' => $staff->random()->id,
                'record_type' => 'seguimiento',
                'chief_complaint' => 'Revisión de prótesis dental y control de rutina',
                'present_illness' => 'Paciente asintomática, viene para revisión de rutina y ajuste de prótesis',
                'medical_history' => 'Osteoporosis, Artritis reumatoide controlada con Metotrexato',
                'dental_history' => 'Prótesis parcial removible superior, múltiples extracciones por enfermedad periodontal',
                'family_history' => 'Madre con osteoporosis severa',
                'social_history' => 'Jubilada, no fuma, no consume alcohol',
                'clinical_examination' => 'Paciente de edad avanzada en buen estado general. Examen oral: prótesis parcial con ajuste deficiente',
                'vital_signs' => 'TA: 140/90 mmHg, FC: 68 lpm, Temp: 36.2°C',
                'oral_examination' => 'Ausencia de dientes posteriores, sobrecarga en dientes anteriores, tejidos blandos sin lesiones',
                'diagnostic_impression' => 'Ajuste deficiente de prótesis, requiere rebase o nueva prótesis',
                'treatment_plan' => "1. Rebasing de prótesis\n2. Ajuste oclusal\n3. Control en 1 mes",
                'recommendations' => 'Limpieza diaria de prótesis, remoción nocturna, dieta blanda',
                'notes' => 'Paciente de edad avanzada, requiere atención especial. Alergia a anestésicos locales.',
                'is_confidential' => false,
                'created_by' => $users->random()->id,
            ]);
        }
    }
}