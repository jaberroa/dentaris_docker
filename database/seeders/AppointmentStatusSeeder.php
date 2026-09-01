<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AppointmentStatus;

class AppointmentStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statuses = [
            [
                'name' => 'scheduled',
                'display_name' => 'Programada',
                'color' => '#3B82F6',
                'icon' => 'calendar',
                'description' => 'Cita programada y pendiente de confirmación',
                'is_active' => true,
                'is_final' => false,
                'sort_order' => 1,
            ],
            [
                'name' => 'confirmed',
                'display_name' => 'Confirmada',
                'color' => '#10B981',
                'icon' => 'check-circle',
                'description' => 'Cita confirmada por el paciente',
                'is_active' => true,
                'is_final' => false,
                'sort_order' => 2,
            ],
            [
                'name' => 'in_progress',
                'display_name' => 'En Progreso',
                'color' => '#F59E0B',
                'icon' => 'clock',
                'description' => 'Cita en curso',
                'is_active' => true,
                'is_final' => false,
                'sort_order' => 3,
            ],
            [
                'name' => 'completed',
                'display_name' => 'Completada',
                'color' => '#059669',
                'icon' => 'check',
                'description' => 'Cita completada exitosamente',
                'is_active' => true,
                'is_final' => true,
                'sort_order' => 4,
            ],
            [
                'name' => 'cancelled',
                'display_name' => 'Cancelada',
                'color' => '#EF4444',
                'icon' => 'x-circle',
                'description' => 'Cita cancelada',
                'is_active' => true,
                'is_final' => true,
                'sort_order' => 5,
            ],
            [
                'name' => 'no_show',
                'display_name' => 'No se Presentó',
                'color' => '#6B7280',
                'icon' => 'user-x',
                'description' => 'Paciente no se presentó a la cita',
                'is_active' => true,
                'is_final' => true,
                'sort_order' => 6,
            ],
            [
                'name' => 'rescheduled',
                'display_name' => 'Reprogramada',
                'color' => '#8B5CF6',
                'icon' => 'refresh-cw',
                'description' => 'Cita reprogramada para otra fecha',
                'is_active' => true,
                'is_final' => false,
                'sort_order' => 7,
            ],
        ];

        foreach ($statuses as $status) {
            AppointmentStatus::updateOrCreate(
                ['name' => $status['name']],
                $status
            );
        }

        $this->command->info('✅ Estados de citas creados exitosamente');
    }
}