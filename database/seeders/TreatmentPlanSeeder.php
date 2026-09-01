<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TreatmentPlan;
use App\Models\TreatmentPlanItem;
use App\Models\Patient;
use App\Models\Staff;
use App\Models\CdtCatalog;
use App\Models\User;
use Carbon\Carbon;

class TreatmentPlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Creando planes de tratamiento...');

        // Obtener datos necesarios
        $patients = Patient::all();
        $staff = Staff::with('user')->get();
        $cdtCatalog = CdtCatalog::all();
        $users = User::all();

        if ($patients->isEmpty() || $staff->isEmpty() || $cdtCatalog->isEmpty()) {
            $this->command->warn('No hay pacientes, personal médico o catálogo CDT disponible. Ejecuta los seeders correspondientes primero.');
            return;
        }

        // Nombres de planes de tratamiento
        $planNames = [
            'Plan de Rehabilitación Oral Completa',
            'Tratamiento de Caries Múltiples',
            'Plan de Ortodoncia',
            'Rehabilitación con Implantes',
            'Tratamiento Periodontal',
            'Plan de Endodoncias',
            'Rehabilitación Estética',
            'Plan de Mantenimiento',
            'Tratamiento de Urgencia',
            'Plan de Prótesis',
            'Tratamiento Conservador',
            'Plan de Blanqueamiento',
            'Rehabilitación Funcional',
            'Plan de Cirugía Oral',
            'Tratamiento Integral',
        ];

        // Descripciones de planes
        $descriptions = [
            'Plan integral para rehabilitar la función masticatoria y estética del paciente',
            'Tratamiento conservador para restaurar dientes afectados por caries',
            'Plan ortodóntico para corregir maloclusiones y alinear dientes',
            'Rehabilitación oral mediante colocación de implantes dentales',
            'Tratamiento de enfermedad periodontal con seguimiento',
            'Plan de tratamientos endodónticos para dientes con afectación pulpar',
            'Rehabilitación estética para mejorar la sonrisa del paciente',
            'Plan de mantenimiento y prevención de salud oral',
            'Tratamiento de urgencia para resolver problemas agudos',
            'Plan de prótesis para reemplazar dientes faltantes',
            'Tratamiento conservador con enfoque preventivo',
            'Plan de blanqueamiento dental para mejorar estética',
            'Rehabilitación funcional para restaurar función masticatoria',
            'Plan de cirugía oral para extracciones y procedimientos quirúrgicos',
            'Tratamiento integral que abarca múltiples especialidades',
        ];

        // Estados de planes
        $statuses = ['draft', 'active', 'completed', 'cancelled'];
        $statusWeights = [20, 50, 25, 5]; // Porcentajes de distribución

        $plansCreated = 0;

        for ($i = 0; $i < 100; $i++) {
            $patient = $patients->random();
            $staffMember = $staff->random();
            $user = $users->random();
            $planName = $planNames[array_rand($planNames)];
            $description = $descriptions[array_rand($descriptions)];
            
            // Seleccionar estado basado en pesos
            $status = $this->getWeightedRandomStatus($statuses, $statusWeights);

            // Generar fechas
            $startDate = Carbon::now()->subDays(rand(0, 90));
            $endDate = $startDate->copy()->addDays(rand(30, 365));

            // Calcular costo total y sesiones
            $totalSessions = rand(1, 12);
            $baseCost = $staffMember->consultation_fee * 2;
            $totalCost = $baseCost * rand(3, 15);

            // Crear el plan de tratamiento
            $treatmentPlan = TreatmentPlan::create([
                'patient_id' => $patient->id,
                'staff_id' => $staffMember->id,
                'plan_name' => $planName,
                'description' => $description,
                'status' => $status,
                'start_date' => $status === 'draft' ? null : $startDate,
                'end_date' => $status === 'draft' ? null : $endDate,
                'total_sessions' => $totalSessions,
                'total_cost' => $totalCost,
                'notes' => fake()->optional(0.7)->paragraph(),
                'is_urgent' => fake()->boolean(10), // 10% de probabilidad
                'requires_approval' => fake()->boolean(20), // 20% de probabilidad
                'approved_at' => $status === 'active' ? fake()->dateTimeBetween($startDate, 'now') : null,
                'approved_by' => $status === 'active' ? $users->random()->id : null,
                'created_by' => $user->id,
            ]);

            // Crear items del plan de tratamiento
            $this->createTreatmentPlanItems($treatmentPlan, $cdtCatalog, $users);

            $plansCreated++;
        }

        $this->command->info('✅ Planes de tratamiento creados exitosamente:');
        $this->command->info("- {$plansCreated} planes de tratamiento");
        $this->command->info('- Con items detallados y procedimientos CDT');
        $this->command->info('- Diferentes estados y fechas realistas');
    }

    /**
     * Seleccionar estado basado en pesos
     */
    private function getWeightedRandomStatus($statuses, $weights)
    {
        $totalWeight = array_sum($weights);
        $random = rand(1, $totalWeight);
        
        $currentWeight = 0;
        foreach ($statuses as $index => $status) {
            $currentWeight += $weights[$index];
            if ($random <= $currentWeight) {
                return $status;
            }
        }
        
        return $statuses[0]; // Fallback
    }

    /**
     * Crear items del plan de tratamiento
     */
    private function createTreatmentPlanItems($treatmentPlan, $cdtCatalog, $users)
    {
        $numItems = rand(2, 8); // Entre 2 y 8 items por plan
        $selectedProcedures = $cdtCatalog->random($numItems);
        
        $itemStatuses = ['pending', 'in_progress', 'completed', 'cancelled'];
        $statusWeights = [30, 20, 45, 5]; // Porcentajes de distribución

        foreach ($selectedProcedures as $index => $procedure) {
            $sequenceOrder = $index + 1;
            $quantity = rand(1, 3);
            $unitPrice = $procedure->base_price * (0.8 + (rand(0, 40) / 100)); // Variación del 80% al 120%
            $totalPrice = $quantity * $unitPrice;
            
            $itemStatus = $this->getWeightedRandomStatus($itemStatuses, $statusWeights);

            $item = TreatmentPlanItem::create([
                'treatment_plan_id' => $treatmentPlan->id,
                'cdt_catalog_id' => $procedure->id,
                'sequence_order' => $sequenceOrder,
                'item_name' => $procedure->procedure_name,
                'description' => $procedure->description,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'total_price' => $totalPrice,
                'estimated_duration' => $procedure->estimated_duration,
                'status' => $itemStatus,
                'notes' => fake()->optional(0.6)->sentence(),
                'is_optional' => fake()->boolean(15), // 15% de probabilidad
                'requires_anesthesia' => $procedure->requires_anesthesia,
                'scheduled_date' => $itemStatus !== 'pending' ? fake()->dateTimeBetween($treatmentPlan->start_date ?? now(), 'now') : null,
            ]);
        }

        // Actualizar el costo total del plan basado en los items
        $actualTotalCost = $treatmentPlan->items()->sum('total_price');
        $treatmentPlan->update(['total_cost' => $actualTotalCost]);
    }
}
