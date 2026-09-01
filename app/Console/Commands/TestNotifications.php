<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\NotificationService;
use App\Models\User;

class TestNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Probar el sistema de notificaciones';

    /**
     * Execute the console command.
     */
    public function handle(NotificationService $notificationService)
    {
        $this->info('Probando sistema de notificaciones...');

        // Obtener un usuario para probar
        $user = User::first();
        if (!$user) {
            $this->error('No hay usuarios en la base de datos.');
            return;
        }

        $this->info("Probando con usuario: {$user->name} ({$user->email})");

        // Datos de prueba
        $data = [
            'patient_name' => 'Paciente de Prueba',
            'appointment_date' => now()->format('d/m/Y'),
            'appointment_time' => now()->format('H:i'),
            'staff_name' => 'Dr. Prueba',
            'clinic_name' => config('app.name'),
            'clinic_phone' => config('app.phone', '+1234567890'),
            'clinic_address' => config('app.address', 'Calle Principal 123'),
        ];

        // Probar notificación de email
        $this->info('Enviando notificación de prueba por email...');
        $success = $notificationService->sendNotification(
            $user->email,
            'appointment_reminder',
            'appointment_reminder_24_hour',
            $data,
            'email'
        );

        if ($success) {
            $this->info('✅ Notificación de email enviada exitosamente');
        } else {
            $this->error('❌ Error al enviar notificación de email');
        }

        // Probar notificación de WhatsApp
        $this->info('Enviando notificación de prueba por WhatsApp...');
        $success = $notificationService->sendNotification(
            $user->phone ?? '+1234567890',
            'appointment_reminder',
            'whatsapp_appointment_reminder',
            $data,
            'whatsapp'
        );

        if ($success) {
            $this->info('✅ Notificación de WhatsApp enviada exitosamente');
        } else {
            $this->error('❌ Error al enviar notificación de WhatsApp');
        }

        // Probar notificación de SMS
        $this->info('Enviando notificación de prueba por SMS...');
        $success = $notificationService->sendNotification(
            $user->phone ?? '+1234567890',
            'appointment_reminder',
            'sms_appointment_reminder',
            $data,
            'sms'
        );

        if ($success) {
            $this->info('✅ Notificación de SMS enviada exitosamente');
        } else {
            $this->error('❌ Error al enviar notificación de SMS');
        }

        // Mostrar estadísticas
        $stats = $notificationService->getNotificationStats();
        $this->info('📊 Estadísticas de notificaciones:');
        $this->table(
            ['Métrica', 'Valor'],
            [
                ['Total Enviadas', $stats['total_sent']],
                ['Total Fallidas', $stats['total_failed']],
                ['Pendientes', $stats['pending']],
            ]
        );

        $this->info('Prueba de notificaciones completada.');
    }
}