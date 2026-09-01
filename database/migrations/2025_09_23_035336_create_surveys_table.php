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
        Schema::create('surveys', function (Blueprint $table) {
            $table->id();
            $table->string('survey_name'); // Nombre de la encuesta
            $table->text('description')->nullable(); // Descripción
            $table->enum('type', ['nps', 'satisfaction', 'feedback', 'custom']); // Tipo de encuesta
            $table->enum('target_audience', ['patients', 'staff', 'both']); // Audiencia objetivo
            $table->enum('status', ['draft', 'active', 'inactive', 'closed'])->default('draft'); // Estado
            $table->date('start_date')->nullable(); // Fecha de inicio
            $table->date('end_date')->nullable(); // Fecha de fin
            $table->boolean('is_anonymous')->default(false); // Es anónima
            $table->boolean('requires_login')->default(false); // Requiere login
            $table->integer('max_responses')->nullable(); // Máximo de respuestas
            $table->text('instructions')->nullable(); // Instrucciones
            $table->json('settings')->nullable(); // Configuraciones adicionales
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('surveys');
    }
};
