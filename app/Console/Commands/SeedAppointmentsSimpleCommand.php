<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Staff;
use App\Models\AppointmentStatus;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SeedAppointmentsSimpleCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'seed:appointments-simple {--count=100 : Number of appointments to create}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate test appointments using existing data';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $count = (int) $this->option('count');
        
        $this->info("Generating {$count} test appointments using existing data...");
        
        // Verificar datos existentes
        $patients = Patient::all();
        $users = User::all();
        $statuses = AppointmentStatus::all();
        
        if ($patients->count() == 0) {
            $this->error("❌ No patients found. Please create patients first.");
            return 1;
        }
        
        if ($users->count() == 0) {
            $this->error("❌ No users found. Please create users first.");
            return 1;
        }
        
        if ($statuses->count() == 0) {
            $this->error("❌ No appointment statuses found. Please create statuses first.");
            return 1;
        }
        
        $this->info("✅ Found {$patients->count()} patients, {$users->count()} users, {$statuses->count()} statuses");
        
        // Crear personal médico si no existe
        if (Staff::count() == 0) {
            $this->info("Creating staff members...");
            $this->createStaff($users);
        }
        
        $staff = Staff::all();
        $this->info("✅ Found {$staff->count()} staff members");
        
        // Crear appointments
        $this->createAppointments($count, $patients, $staff, $statuses, $users);
        
        $this->info("✅ Successfully created {$count} test appointments!");
        
        return 0;
    }
    
    /**
     * Create staff members
     */
    private function createStaff($users)
    {
        $staffData = [
            [
                'user_id' => $users->first()->id,
                'employee_id' => 'EMP001',
                'specialty' => 'Odontología General',
                'license_number' => 'OD001',
                'license_expiry' => '2025-12-31',
                'university' => 'Universidad Nacional',
                'graduation_year' => 2015,
                'bio' => 'Especialista en odontología general con más de 8 años de experiencia.',
                'consultation_fee' => 80000,
                'experience_years' => 8,
                'languages' => json_encode(['Español', 'Inglés']),
                'certifications' => json_encode(['Odontología General', 'Endodoncia']),
                'is_available' => true,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'user_id' => $users->skip(1)->first()?->id ?? $users->first()->id,
                'employee_id' => 'EMP002',
                'specialty' => 'Ortodoncia',
                'license_number' => 'OD002',
                'license_expiry' => '2025-12-31',
                'university' => 'Universidad Javeriana',
                'graduation_year' => 2018,
                'bio' => 'Especialista en ortodoncia y ortopedia maxilar.',
                'consultation_fee' => 120000,
                'experience_years' => 5,
                'languages' => json_encode(['Español', 'Inglés']),
                'certifications' => json_encode(['Ortodoncia', 'Ortopedia Maxilar']),
                'is_available' => true,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'user_id' => $users->skip(2)->first()?->id ?? $users->first()->id,
                'employee_id' => 'EMP003',
                'specialty' => 'Endodoncia',
                'license_number' => 'OD003',
                'license_expiry' => '2025-12-31',
                'university' => 'Universidad del Valle',
                'graduation_year' => 2020,
                'bio' => 'Especialista en endodoncia y tratamiento de conductos.',
                'consultation_fee' => 150000,
                'experience_years' => 3,
                'languages' => json_encode(['Español']),
                'certifications' => json_encode(['Endodoncia', 'Microcirugía']),
                'is_available' => true,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];
        
        DB::table('staff')->insert($staffData);
    }
    
    /**
     * Create test appointments
     */
    private function createAppointments($count, $patients, $staff, $statuses, $users)
    {
        $this->info("Creating {$count} appointments...");
        
        $appointmentTypes = [
            'Consulta General',
            'Limpieza Dental',
            'Endodoncia',
            'Extracción',
            'Ortodoncia',
            'Periodoncia',
            'Prostodoncia',
            'Cirugía Oral',
            'Radiografía',
            'Tratamiento de Caries',
            'Blanqueamiento',
            'Implante Dental',
            'Revisión',
            'Urgencia',
            'Seguimiento'
        ];
        
        $reasons = [
            'Dolor dental',
            'Revisión rutinaria',
            'Limpieza dental',
            'Tratamiento de caries',
            'Extracción de muela',
            'Endodoncia',
            'Ortodoncia',
            'Implante dental',
            'Problema de encías',
            'Blanqueamiento',
            'Cirugía oral',
            'Urgencia dental',
            'Seguimiento post-tratamiento',
            'Consulta de ortodoncia',
            'Problema de prótesis'
        ];
        
        $progressBar = $this->output->createProgressBar($count);
        $progressBar->start();
        
        $appointments = [];
        
        for ($i = 0; $i < $count; $i++) {
            $startDate = Carbon::now()->addDays(rand(-30, 60));
            $startTime = Carbon::createFromTime(rand(8, 17), [0, 30][rand(0, 1)], 0);
            $endTime = $startTime->copy()->addMinutes(rand(30, 180));
            
            $status = $statuses->random();
            $isCancelled = $status->name === 'Cancelada';
            
            $appointment = [
                'appointment_code' => 'APT-' . str_pad($i + 1, 6, '0', STR_PAD_LEFT),
                'patient_id' => $patients->random()->id,
                'staff_id' => $staff->random()->id,
                'appointment_status_id' => $status->id,
                'appointment_date' => $startDate->format('Y-m-d'),
                'start_time' => $startTime->format('H:i:s'),
                'end_time' => $endTime->format('H:i:s'),
                'duration' => $startTime->diffInMinutes($endTime),
                'type' => $appointmentTypes[array_rand($appointmentTypes)],
                'reason' => $reasons[array_rand($reasons)],
                'notes' => $this->generateNotes(),
                'treatment_plan' => $this->generateTreatmentPlan(),
                'estimated_cost' => rand(50000, 500000),
                'is_urgent' => rand(0, 1),
                'is_follow_up' => rand(0, 1),
                'is_recurring' => rand(0, 1),
                'reminder_sent' => rand(0, 1),
                'parent_appointment_id' => null,
                'confirmed_at' => $status->name === 'Confirmada' ? Carbon::now()->subDays(rand(1, 30)) : null,
                'cancelled_at' => $isCancelled ? Carbon::now()->subDays(rand(1, 10)) : null,
                'cancellation_reason' => $isCancelled ? $this->getCancellationReason() : null,
                'created_by' => $users->random()->id,
                'created_at' => Carbon::now()->subDays(rand(1, 30)),
                'updated_at' => Carbon::now()->subDays(rand(1, 30)),
            ];
            
            $appointments[] = $appointment;
            $progressBar->advance();
        }
        
        // Insertar en lotes
        $chunks = array_chunk($appointments, 50);
        foreach ($chunks as $chunk) {
            DB::table('appointments')->insert($chunk);
        }
        
        $progressBar->finish();
        $this->newLine();
    }
    
    /**
     * Generate random notes
     */
    private function generateNotes()
    {
        $notes = [
            'Paciente requiere atención especial',
            'Primera consulta',
            'Seguimiento de tratamiento anterior',
            'Paciente con ansiedad dental',
            'Requiere anestesia local',
            'Paciente alérgico a ciertos medicamentos',
            'Consulta de segunda opinión',
            'Tratamiento de emergencia',
            'Paciente menor de edad - requiere acompañante',
            'Consulta para prótesis dental',
            'Revisión post-operatoria',
            'Tratamiento de ortodoncia',
            'Limpieza profunda requerida',
            'Paciente con diabetes - precauciones especiales',
            'Consulta de blanqueamiento dental',
            null, null, null // Agregar algunos nulos para variedad
        ];
        
        return $notes[array_rand($notes)];
    }
    
    /**
     * Generate treatment plan
     */
    private function generateTreatmentPlan()
    {
        $plans = [
            'Plan de tratamiento conservador',
            'Plan de rehabilitación oral completa',
            'Plan de ortodoncia integral',
            'Plan de mantenimiento periodontal',
            'Plan de prótesis dental',
            'Plan de endodoncia',
            'Plan de cirugía oral',
            'Plan de blanqueamiento dental',
            'Plan de prevención y mantenimiento',
            'Plan de tratamiento de emergencia',
            null, null, null // Agregar algunos nulos para variedad
        ];
        
        return $plans[array_rand($plans)];
    }
    
    /**
     * Get cancellation reason
     */
    private function getCancellationReason()
    {
        $reasons = [
            'Paciente canceló por motivos personales',
            'Emergencia médica del paciente',
            'Conflicto de horario',
            'Problema de transporte',
            'Cambio de disponibilidad del doctor',
            'Paciente no pudo asistir',
            'Reprogramación solicitada',
            'Emergencia familiar'
        ];
        
        return $reasons[array_rand($reasons)];
    }
}
