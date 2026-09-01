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
        Schema::create('survey_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('survey_id')->constrained()->onDelete('cascade');
            $table->string('question_text'); // Texto de la pregunta
            $table->text('description')->nullable(); // Descripción de la pregunta
            $table->enum('question_type', ['text', 'number', 'rating', 'multiple_choice', 'single_choice', 'yes_no', 'nps']); // Tipo de pregunta
            $table->json('options')->nullable(); // Opciones para preguntas de selección
            $table->boolean('is_required')->default(false); // Es obligatoria
            $table->integer('sort_order')->default(0); // Orden de aparición
            $table->json('validation_rules')->nullable(); // Reglas de validación
            $table->text('help_text')->nullable(); // Texto de ayuda
            $table->boolean('is_active')->default(true); // Activa
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('survey_questions');
    }
};
