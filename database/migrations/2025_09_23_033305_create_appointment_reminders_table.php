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
        Schema::create('appointment_reminders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appointment_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['email', 'sms', 'whatsapp', 'call']); // Tipo de recordatorio
            $table->integer('minutes_before'); // Minutos antes de la cita
            $table->text('message')->nullable(); // Mensaje personalizado
            $table->timestamp('sent_at')->nullable(); // Fecha de envío
            $table->enum('status', ['pending', 'sent', 'failed', 'cancelled'])->default('pending');
            $table->text('error_message')->nullable(); // Mensaje de error si falla
            $table->integer('retry_count')->default(0); // Intentos de reenvío
            $table->timestamp('next_retry_at')->nullable(); // Próximo intento
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointment_reminders');
    }
};
