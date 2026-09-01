<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->string('notification_id')->unique(); // ID único de la notificación
            $table->enum('type', ['appointment_reminder', 'payment_reminder', 'appointment_confirmation', 'payment_confirmation', 'lab_work_ready', 'general']); // Tipo de notificación
            $table->enum('channel', ['email', 'sms', 'whatsapp', 'push', 'in_app']); // Canal de notificación
            $table->string('recipient_type'); // Tipo de destinatario (patient, staff, admin)
            $table->unsignedBigInteger('recipient_id'); // ID del destinatario
            $table->string('recipient_contact'); // Contacto del destinatario (email, teléfono)
            $table->string('subject')->nullable(); // Asunto
            $table->text('message'); // Mensaje
            $table->json('variables')->nullable(); // Variables del mensaje
            $table->enum('status', ['pending', 'sent', 'delivered', 'failed', 'cancelled'])->default('pending');
            $table->timestamp('scheduled_at')->nullable(); // Fecha programada
            $table->timestamp('sent_at')->nullable(); // Fecha de envío
            $table->timestamp('delivered_at')->nullable(); // Fecha de entrega
            $table->text('error_message')->nullable(); // Mensaje de error
            $table->integer('retry_count')->default(0); // Contador de reintentos
            $table->timestamp('next_retry_at')->nullable(); // Próximo reintento
            $table->json('metadata')->nullable(); // Metadatos adicionales
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
