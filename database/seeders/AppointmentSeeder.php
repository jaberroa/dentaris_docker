<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Staff;
use App\Models\AppointmentStatus;
use App\Models\User;
use Carbon\Carbon;

class AppointmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Creando citas de prueba...');

        // Obtener datos necesarios
        $patients = Patient::all();
        $staff = Staff::with('user')->get();
        $statuses = AppointmentStatus::all();
        $users = User::all();

        if ($patients->isEmpty() || $staff->isEmpty() || $statuses->isEmpty()) {
            $this->command->warn('No hay pacientes, personal médico o estados disponibles. Ejecuta los seeders correspondientes primero.');
            return;
        }

        // Tipos de citas
        $appointmentTypes = [
            'Consulta General',
            'Limpieza Dental',
            'Extracción',
            'Empaste',
            'Endodoncia',
            'Ortodoncia',
            'Implante',
            'Cirugía',
            'Revisión',
            'Seguimiento',
            'Urgencia',
            'Profilaxis',
            'Blanqueamiento',
            'Corona',
            'Puente',
        ];

        // Motivos de citas
        $reasons = [
            'Dolor dental',
            'Revisión rutinaria',
            'Limpieza dental',
            'Tratamiento de caries',
            'Extracción de muela',
            'Tratamiento de conducto',
            'Ajuste de brackets',
            'Colocación de implante',
            'Cirugía oral',
            'Consulta de urgencia',
            'Seguimiento post-tratamiento',
            'Blanqueamiento dental',
            'Colocación de corona',
            'Reparación de prótesis',
            'Consulta estética',
        ];

        // Planes de tratamiento
        $treatmentPlans = [
            'Tratamiento conservador con seguimiento cada 6 meses',
            'Tratamiento de endodoncia con corona final',
            'Tratamiento ortodóntico de 18-24 meses',
            'Tratamiento de implante con carga inmediata',
            'Tratamiento de cirugía periodontal',
            'Tratamiento de blanqueamiento con seguimiento',
            'Tratamiento de prótesis fija',
            'Tratamiento de urgencia con seguimiento',
            'Tratamiento preventivo',
            'Tratamiento estético integral',
        ];

        // Crear citas para los próximos 30 días
        $startDate = Carbon::now()->startOfDay();
        $endDate = Carbon::now()->addDays(30)->endOfDay();

        $appointmentsCreated = 0;

        for ($i = 0; $i < 150; $i++) {
            $patient = $patients->random();
            $staffMember = $staff->random();
            $status = $statuses->random();
            $user = $users->random();

            // Generar fecha aleatoria en el rango
            $appointmentDate = Carbon::createFromTimestamp(
                rand($startDate->timestamp, $endDate->timestamp)
            )->startOfDay();

            // Generar hora de inicio (8:00 AM a 6:00 PM)
            $startHour = rand(8, 17);
            $startMinute = rand(0, 3) * 15; // 0, 15, 30, 45
            $startTime = Carbon::createFromTime($startHour, $startMinute);

            // Duración aleatoria (15, 30, 45, 60, 90 minutos)
            $duration = collect([15, 30, 45, 60, 90])->random();
            $endTime = $startTime->copy()->addMinutes($duration);

            // Verificar que no sea en el pasado (excepto para citas completadas/canceladas)
            if ($appointmentDate->isPast() && !in_array($status->name, ['completed', 'cancelled', 'no_show'])) {
                $appointmentDate = Carbon::now()->addDays(rand(1, 30));
            }

            // Generar código único
            $appointmentCode = 'CIT-' . strtoupper(uniqid());

            // Costo estimado basado en el tipo de cita
            $baseCost = $staffMember->consultation_fee;
            $costMultiplier = match (true) {
                str_contains($appointmentTypes[array_rand($appointmentTypes)], 'Cirugía') => 3.0,
                str_contains($appointmentTypes[array_rand($appointmentTypes)], 'Implante') => 2.5,
                str_contains($appointmentTypes[array_rand($appointmentTypes)], 'Ortodoncia') => 2.0,
                str_contains($appointmentTypes[array_rand($appointmentTypes)], 'Endodoncia') => 1.8,
                str_contains($appointmentTypes[array_rand($appointmentTypes)], 'Urgencia') => 1.5,
                default => 1.0,
            };

            $estimatedCost = $baseCost * $costMultiplier;

            // Crear la cita
            $appointment = Appointment::create([
                'appointment_code' => $appointmentCode,
                'patient_id' => $patient->id,
                'staff_id' => $staffMember->id,
                'appointment_status_id' => $status->id,
                'appointment_date' => $appointmentDate->format('Y-m-d'),
                'start_time' => $startTime->format('H:i:s'),
                'end_time' => $endTime->format('H:i:s'),
                'duration' => $duration,
                'type' => $appointmentTypes[array_rand($appointmentTypes)],
                'reason' => $reasons[array_rand($reasons)],
                'notes' => fake()->optional(0.7)->paragraph(),
                'treatment_plan' => fake()->optional(0.6)->randomElement($treatmentPlans),
                'estimated_cost' => $estimatedCost,
                'is_urgent' => fake()->boolean(10), // 10% de probabilidad
                'is_follow_up' => fake()->boolean(15), // 15% de probabilidad
                'confirmed_at' => $status->name === 'confirmed' ? fake()->dateTimeBetween('-1 week', 'now') : null,
                'cancelled_at' => in_array($status->name, ['cancelled', 'no_show']) ? fake()->dateTimeBetween('-1 week', 'now') : null,
                'cancellation_reason' => in_array($status->name, ['cancelled', 'no_show']) ? fake()->randomElement([
                    'Paciente canceló',
                    'Emergencia médica',
                    'Conflicto de horario',
                    'No se presentó',
                    'Clima adverso',
                    'Problema de transporte',
                ]) : null,
                'created_by' => $user->id,
            ]);

            $appointmentsCreated++;
        }

        // Crear algunas citas de seguimiento
        $parentAppointments = Appointment::where('is_follow_up', false)->take(20)->get();
        foreach ($parentAppointments as $parent) {
            $followUpDate = Carbon::parse($parent->appointment_date)->addDays(rand(7, 30));
            $followUpStatus = AppointmentStatus::where('name', 'scheduled')->first();
            
            if ($followUpDate->isFuture()) {
                Appointment::create([
                    'appointment_code' => 'CIT-' . strtoupper(uniqid()),
                    'patient_id' => $parent->patient_id,
                    'staff_id' => $parent->staff_id,
                    'appointment_status_id' => $followUpStatus->id,
                    'appointment_date' => $followUpDate->format('Y-m-d'),
                    'start_time' => $parent->start_time,
                    'end_time' => $parent->end_time,
                    'duration' => $parent->duration,
                    'type' => 'Seguimiento',
                    'reason' => 'Cita de seguimiento post-tratamiento',
                    'notes' => 'Seguimiento de tratamiento realizado el ' . $parent->appointment_date,
                    'treatment_plan' => 'Evaluación de resultados y plan de mantenimiento',
                    'estimated_cost' => $parent->estimated_cost * 0.5, // 50% del costo original
                    'is_urgent' => false,
                    'is_follow_up' => true,
                    'parent_appointment_id' => $parent->id,
                    'created_by' => $parent->created_by,
                ]);
            }
        }

        $this->command->info('✅ Citas creadas exitosamente:');
        $this->command->info("- {$appointmentsCreated} citas principales");
        $this->command->info('- 20 citas de seguimiento');
        $this->command->info('- Distribuidas en los próximos 30 días');
        $this->command->info('- Con diferentes estados y tipos');
    }
}





