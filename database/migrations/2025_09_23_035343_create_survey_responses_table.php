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
        Schema::create('survey_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('survey_id')->constrained()->onDelete('cascade');
            $table->foreignId('question_id')->constrained('survey_questions')->onDelete('cascade');
            $table->foreignId('patient_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('staff_id')->nullable()->constrained()->onDelete('set null');
            $table->string('response_value'); // Valor de la respuesta
            $table->text('response_text')->nullable(); // Texto de la respuesta
            $table->decimal('numeric_value', 10, 2)->nullable(); // Valor numérico
            $table->json('multiple_values')->nullable(); // Valores múltiples
            $table->enum('response_type', ['text', 'number', 'rating', 'choice', 'boolean']); // Tipo de respuesta
            $table->timestamp('submitted_at'); // Fecha de envío
            $table->string('ip_address')->nullable(); // Dirección IP
            $table->text('user_agent')->nullable(); // User agent
            $table->boolean('is_anonymous')->default(false); // Es anónima
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('survey_responses');
    }
};
