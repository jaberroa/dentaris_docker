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
        Schema::create('notification_queue', function (Blueprint $table) {
            $table->id();
            $table->foreignId('notification_id')->constrained()->onDelete('cascade');
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal'); // Prioridad
            $table->timestamp('scheduled_at'); // Fecha programada
            $table->integer('attempts')->default(0); // Intentos realizados
            $table->integer('max_attempts')->default(3); // Máximo de intentos
            $table->timestamp('last_attempt_at')->nullable(); // Último intento
            $table->timestamp('processed_at')->nullable(); // Fecha de procesamiento
            $table->enum('status', ['pending', 'processing', 'completed', 'failed', 'cancelled'])->default('pending');
            $table->text('error_message')->nullable(); // Mensaje de error
            $table->json('context')->nullable(); // Contexto adicional
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_queue');
    }
};
