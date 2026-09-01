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

class SeedAppointmentsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'seed:appointments {--count=100 : Number of appointments to create}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate test data for appointments module';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $count = (int) $this->option('count');
        
        $this->info("Generating {$count} test appointments...");
        
        // Verificar que existan los datos necesarios
        $this->ensureRequiredData();
        
        // Crear appointments
        $this->createAppointments($count);
        
        $this->info("✅ Successfully created {$count} test appointments!");
        
        return 0;
    }
    
    /**
     * Ensure required data exists
     */
    private function ensureRequiredData()
    {
        $this->info("Ensuring required data exists...");
        
        // Crear estados de citas si no existen
        $statuses = [
            [
                'name' => 'Pendiente', 
                'display_name' => 'Pendiente',
                'color' => '#ffc107', 
                'description' => 'Cita pendiente de confirmación',
                'icon' => 'clock',
                'sort_order' => 1
            ],
            [
                'name' => 'Confirmada', 
                'display_name' => 'Confirmada',
                'color' => '#28a745', 
                'description' => 'Cita confirmada',
                'icon' => 'check-circle',
                'sort_order' => 2
            ],
            [
                'name' => 'Cancelada', 
                'display_name' => 'Cancelada',
                'color' => '#dc3545', 
                'description' => 'Cita cancelada',
                'icon' => 'times-circle',
                'sort_order' => 3
            ],
            [
                'name' => 'Completada', 
                'display_name' => 'Completada',
                'color' => '#007bff', 
                'description' => 'Cita completada',
                'icon' => 'check-double',
                'sort_order' => 4
            ],
            [
                'name' => 'Reprogramada', 
                'display_name' => 'Reprogramada',
                'color' => '#6c757d', 
                'description' => 'Cita reprogramada',
                'icon' => 'calendar-alt',
                'sort_order' => 5
            ],
            [
                'name' => 'No Asistió', 
                'display_name' => 'No Asistió',
                'color' => '#6f42c1', 
                'description' => 'Paciente no asistió',
                'icon' => 'user-times',
                'sort_order' => 6
            ],
            [
                'name' => 'En Curso', 
                'display_name' => 'En Curso',
                'color' => '#17a2b8', 
                'description' => 'Cita en curso',
                'icon' => 'spinner',
                'sort_order' => 7
            ],
        ];
        
        foreach ($statuses as $status) {
            AppointmentStatus::firstOrCreate(
                ['name' => $status['name']],
                $status
            );
        }
        
        // Crear usuarios si no existen
        if (User::count() == 0) {
            User::factory()->count(5)->create();
        }
        
        // Crear pacientes si no existen
        if (Patient::count() == 0) {
            Patient::factory()->count(50)->create();
        }
        
        // Crear personal médico si no existe
        if (Staff::count() == 0) {
            Staff::factory()->count(10)->create();
        }
        
        $this->info("✅ Required data ensured");
    }
    
    /**
     * Create test appointments
     */
    private function createAppointments($count)
    {
        $this->info("Creating {$count} appointments...");
        
        $patients = Patient::all();
        $staff = Staff::all();
        $statuses = AppointmentStatus::all();
        $users = User::all();
        
        if ($patients->count() == 0 || $staff->count() == 0 || $statuses->count() == 0) {
            $this->error("❌ Required data not found. Please ensure patients, staff, and statuses exist.");
            return;
        }
        
        $progressBar = $this->output->createProgressBar($count);
        $progressBar->start();
        
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
        
        $appointments = [];
        
        for ($i = 0; $i < $count; $i++) {
            $startDate = Carbon::now()->addDays(rand(-30, 60)); // Fechas entre 30 días atrás y 60 días adelante
            $startTime = Carbon::createFromTime(rand(8, 17), [0, 30][rand(0, 1)], 0);
            $endTime = $startTime->copy()->addMinutes(rand(30, 180)); // Duración entre 30 min y 3 horas
            
            $appointment = [
                'appointment_code' => 'APT-' . str_pad($i + 1, 6, '0', STR_PAD_LEFT),
                'patient_id' => $patients->random()->id,
                'staff_id' => $staff->random()->id,
                'appointment_status_id' => $statuses->random()->id,
                'appointment_date' => $startDate->format('Y-m-d'),
                'start_time' => $startTime->format('H:i:s'),
                'end_time' => $endTime->format('H:i:s'),
                'duration' => $startTime->diffInMinutes($endTime),
                'type' => $appointmentTypes[array_rand($appointmentTypes)],
                'reason' => $reasons[array_rand($reasons)],
                'notes' => $this->generateNotes(),
                'treatment_plan' => $this->generateTreatmentPlan(),
                'estimated_cost' => rand(50000, 500000), // Entre $50,000 y $500,000 COP
                'is_urgent' => rand(0, 1),
                'is_follow_up' => rand(0, 1),
                'is_recurring' => rand(0, 1),
                'reminder_sent' => rand(0, 1),
                'parent_appointment_id' => null, // Se puede establecer después si es seguimiento
                'confirmed_at' => rand(0, 1) ? Carbon::now()->subDays(rand(1, 30)) : null,
                'cancelled_at' => null,
                'cancellation_reason' => null,
                'created_by' => $users->random()->id,
                'created_at' => Carbon::now()->subDays(rand(1, 30)),
                'updated_at' => Carbon::now()->subDays(rand(1, 30)),
            ];
            
            // Si es una cita cancelada, agregar fecha de cancelación
            if ($appointment['appointment_status_id'] == AppointmentStatus::where('name', 'Cancelada')->first()?->id) {
                $appointment['cancelled_at'] = $appointment['created_at']->copy()->addDays(rand(1, 10));
                $appointment['cancellation_reason'] = [
                    'Paciente canceló',
                    'Emergencia médica',
                    'Conflicto de horario',
                    'Problema de transporte',
                    'Cambio de disponibilidad del doctor'
                ][array_rand([
                    'Paciente canceló',
                    'Emergencia médica', 
                    'Conflicto de horario',
                    'Problema de transporte',
                    'Cambio de disponibilidad del doctor'
                ])];
            }
            
            $appointments[] = $appointment;
            
            $progressBar->advance();
        }
        
        // Insertar en lotes para mejor rendimiento
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
            'Consulta de blanqueamiento dental'
        ];
        
        return rand(0, 1) ? $notes[array_rand($notes)] : null;
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
            'Plan de tratamiento de emergencia'
        ];
        
        return rand(0, 1) ? $plans[array_rand($plans)] : null;
    }
}
