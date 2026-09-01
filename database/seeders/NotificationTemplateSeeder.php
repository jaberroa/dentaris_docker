<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\NotificationTemplate;

class NotificationTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $templates = [
            // Recordatorios de Citas
            [
                'template_code' => 'appointment_reminder_24_hour',
                'template_name' => 'Recordatorio de Cita - 24 Horas',
                'subject' => 'Recordatorio de Cita - Mañana',
                'message_template' => 'Hola {{patient_name}}, tienes una cita programada para mañana {{appointment_date}} a las {{appointment_time}} con {{staff_name}}.',
                'description' => 'Recordatorio enviado 24 horas antes de la cita',
                'type' => 'appointment_reminder',
                'channel' => 'email',
                'is_active' => true,
                'is_system' => true,
                'created_by' => 1,
            ],
            [
                'template_code' => 'appointment_reminder_1_hour',
                'template_name' => 'Recordatorio de Cita - 1 Hora',
                'subject' => 'Recordatorio de Cita - En 1 Hora',
                'message_template' => 'Hola {{patient_name}}, tienes una cita en 1 hora ({{appointment_time}}) con {{staff_name}}.',
                'description' => 'Recordatorio enviado 1 hora antes de la cita',
                'type' => 'appointment_reminder',
                'channel' => 'email',
                'is_active' => true,
                'is_system' => true,
                'created_by' => 1,
            ],
            [
                'template_code' => 'appointment_reminder_same_day',
                'template_name' => 'Recordatorio de Cita - Mismo Día',
                'subject' => 'Recordatorio de Cita - Hoy',
                'message_template' => 'Hola {{patient_name}}, tienes una cita programada para hoy {{appointment_date}} a las {{appointment_time}} con {{staff_name}}.',
                'description' => 'Recordatorio enviado el mismo día de la cita',
                'type' => 'appointment_reminder',
                'channel' => 'email',
                'is_active' => true,
                'is_system' => true,
                'created_by' => 1,
            ],

            // Recordatorios de Pagos
            [
                'template_code' => 'payment_reminder_overdue',
                'template_name' => 'Recordatorio de Pago Vencido',
                'subject' => 'Recordatorio de Pago Vencido',
                'message_template' => 'Hola {{patient_name}}, tienes un pago vencido de ${{balance_due}} de la factura {{invoice_number}} que vence el {{due_date}}.',
                'description' => 'Recordatorio para pagos vencidos',
                'type' => 'payment_reminder',
                'channel' => 'email',
                'is_active' => true,
                'is_system' => true,
                'created_by' => 1,
            ],
            [
                'template_code' => 'payment_reminder_due_soon',
                'template_name' => 'Recordatorio de Pago Próximo a Vencer',
                'subject' => 'Recordatorio de Pago Próximo a Vencer',
                'message_template' => 'Hola {{patient_name}}, tienes un pago de ${{balance_due}} de la factura {{invoice_number}} que vence el {{due_date}}.',
                'description' => 'Recordatorio para pagos próximos a vencer',
                'type' => 'payment_reminder',
                'channel' => 'email',
                'is_active' => true,
                'is_system' => true,
                'created_by' => 1,
            ],
            [
                'template_code' => 'payment_confirmation',
                'template_name' => 'Confirmación de Pago',
                'subject' => 'Confirmación de Pago Recibido',
                'message_template' => 'Hola {{patient_name}}, hemos recibido tu pago de ${{amount}} por la factura {{invoice_number}}. ¡Gracias!',
                'description' => 'Confirmación de pago recibido',
                'type' => 'payment_confirmation',
                'channel' => 'email',
                'is_active' => true,
                'is_system' => true,
                'created_by' => 1,
            ],

            // Alertas de Inventario
            [
                'template_code' => 'inventory_low_stock',
                'template_name' => 'Alerta de Stock Bajo',
                'subject' => 'Alerta de Stock Bajo',
                'message_template' => 'El producto {{product_name}} tiene stock bajo. Stock actual: {{current_stock}}, Stock mínimo: {{minimum_stock}}.',
                'description' => 'Alerta cuando un producto tiene stock bajo',
                'type' => 'inventory_alert',
                'channel' => 'email',
                'is_active' => true,
                'is_system' => true,
                'created_by' => 1,
            ],
            [
                'template_code' => 'inventory_out_of_stock',
                'template_name' => 'Alerta de Stock Agotado',
                'subject' => 'Alerta de Stock Agotado',
                'message_template' => 'El producto {{product_name}} está agotado y requiere reposición inmediata.',
                'description' => 'Alerta cuando un producto está agotado',
                'type' => 'inventory_alert',
                'channel' => 'email',
                'is_active' => true,
                'is_system' => true,
                'created_by' => 1,
            ],
            [
                'template_code' => 'inventory_expiring_soon',
                'template_name' => 'Alerta de Producto Próximo a Vencer',
                'subject' => 'Alerta de Producto Próximo a Vencer',
                'message_template' => 'El producto {{product_name}} está próximo a vencer el {{expiry_date}}. Stock actual: {{current_stock}}.',
                'description' => 'Alerta cuando un producto está próximo a vencer',
                'type' => 'inventory_alert',
                'channel' => 'email',
                'is_active' => true,
                'is_system' => true,
                'created_by' => 1,
            ],

            // Notificaciones Generales
            [
                'template_code' => 'welcome_new_patient',
                'template_name' => 'Bienvenida a Nuevo Paciente',
                'subject' => 'Bienvenido a {{clinic_name}}',
                'message_template' => 'Hola {{patient_name}}, bienvenido a {{clinic_name}}. Estamos aquí para cuidar de tu salud dental.',
                'description' => 'Mensaje de bienvenida para nuevos pacientes',
                'type' => 'welcome',
                'channel' => 'email',
                'is_active' => true,
                'is_system' => true,
                'created_by' => 1,
            ],
            [
                'template_code' => 'appointment_cancelled',
                'template_name' => 'Cita Cancelada',
                'subject' => 'Cita Cancelada',
                'message_template' => 'Hola {{patient_name}}, tu cita del {{appointment_date}} a las {{appointment_time}} ha sido cancelada. {{cancellation_reason}}',
                'description' => 'Notificación cuando se cancela una cita',
                'type' => 'appointment_cancelled',
                'channel' => 'email',
                'is_active' => true,
                'is_system' => true,
                'created_by' => 1,
            ],
            [
                'template_code' => 'appointment_confirmed',
                'template_name' => 'Cita Confirmada',
                'subject' => 'Cita Confirmada',
                'message_template' => 'Hola {{patient_name}}, tu cita del {{appointment_date}} a las {{appointment_time}} con {{staff_name}} ha sido confirmada.',
                'description' => 'Confirmación de cita',
                'type' => 'appointment_confirmed',
                'channel' => 'email',
                'is_active' => true,
                'is_system' => true,
                'created_by' => 1,
            ],

            // Plantillas WhatsApp
            [
                'template_code' => 'whatsapp_appointment_reminder',
                'template_name' => 'Recordatorio WhatsApp',
                'subject' => 'Recordatorio de Cita',
                'message_template' => '🦷 *{{clinic_name}}*\n\nHola {{patient_name}}, tienes una cita programada para {{appointment_date}} a las {{appointment_time}} con {{staff_name}}.\n\n📍 {{clinic_address}}\n📞 {{clinic_phone}}',
                'description' => 'Recordatorio de cita por WhatsApp',
                'type' => 'appointment_reminder',
                'channel' => 'whatsapp',
                'is_active' => true,
                'is_system' => true,
                'created_by' => 1,
            ],
            [
                'template_code' => 'whatsapp_payment_reminder',
                'template_name' => 'Recordatorio de Pago WhatsApp',
                'subject' => 'Recordatorio de Pago',
                'message_template' => '🦷 *{{clinic_name}}*\n\nHola {{patient_name}}, tienes un pago pendiente de ${{balance_due}} de la factura {{invoice_number}} que vence el {{due_date}}.\n\n📞 {{clinic_phone}}',
                'description' => 'Recordatorio de pago por WhatsApp',
                'type' => 'payment_reminder',
                'channel' => 'whatsapp',
                'is_active' => true,
                'is_system' => true,
                'created_by' => 1,
            ],

            // Plantillas SMS
            [
                'template_code' => 'sms_appointment_reminder',
                'template_name' => 'Recordatorio SMS',
                'subject' => 'Recordatorio de Cita',
                'message_template' => '{{clinic_name}}: Hola {{patient_name}}, tienes una cita el {{appointment_date}} a las {{appointment_time}} con {{staff_name}}. {{clinic_phone}}',
                'description' => 'Recordatorio de cita por SMS',
                'type' => 'appointment_reminder',
                'channel' => 'sms',
                'is_active' => true,
                'is_system' => true,
                'created_by' => 1,
            ],
            [
                'template_code' => 'sms_payment_reminder',
                'template_name' => 'Recordatorio de Pago SMS',
                'subject' => 'Recordatorio de Pago',
                'message_template' => '{{clinic_name}}: Hola {{patient_name}}, tienes un pago pendiente de ${{balance_due}} que vence el {{due_date}}. {{clinic_phone}}',
                'description' => 'Recordatorio de pago por SMS',
                'type' => 'payment_reminder',
                'channel' => 'sms',
                'is_active' => true,
                'is_system' => true,
                'created_by' => 1,
            ],
        ];

        foreach ($templates as $template) {
            NotificationTemplate::create($template);
        }
    }
}