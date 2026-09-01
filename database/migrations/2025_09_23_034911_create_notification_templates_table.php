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
        Schema::create('notification_templates', function (Blueprint $table) {
            $table->id();
            $table->string('template_name'); // Nombre de la plantilla
            $table->string('template_code')->unique(); // Código de la plantilla
            $table->enum('type', ['appointment_reminder', 'payment_reminder', 'appointment_confirmation', 'payment_confirmation', 'lab_work_ready', 'general']); // Tipo
            $table->enum('channel', ['email', 'sms', 'whatsapp', 'push', 'in_app']); // Canal
            $table->string('subject')->nullable(); // Asunto
            $table->text('message_template'); // Plantilla del mensaje
            $table->json('variables')->nullable(); // Variables disponibles
            $table->text('description')->nullable(); // Descripción
            $table->boolean('is_active')->default(true); // Activo
            $table->boolean('is_system')->default(false); // Es plantilla del sistema
            $table->json('settings')->nullable(); // Configuraciones específicas
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_templates');
    }
};
