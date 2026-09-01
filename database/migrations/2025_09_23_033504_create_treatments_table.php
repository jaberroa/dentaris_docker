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
        Schema::create('treatments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medical_record_id')->constrained()->onDelete('cascade');
            $table->string('treatment_code')->nullable(); // Código del tratamiento
            $table->string('treatment_name'); // Nombre del tratamiento
            $table->text('description')->nullable(); // Descripción detallada
            $table->enum('type', ['preventive', 'restorative', 'surgical', 'orthodontic', 'endodontic', 'periodontal', 'prosthetic']); // Tipo de tratamiento
            $table->enum('status', ['planned', 'in_progress', 'completed', 'cancelled', 'postponed'])->default('planned');
            $table->date('start_date')->nullable(); // Fecha de inicio
            $table->date('end_date')->nullable(); // Fecha de finalización
            $table->integer('sessions_planned')->default(1); // Sesiones planificadas
            $table->integer('sessions_completed')->default(0); // Sesiones completadas
            $table->decimal('cost', 10, 2)->nullable(); // Costo del tratamiento
            $table->text('materials_used')->nullable(); // Materiales utilizados
            $table->text('procedure_notes')->nullable(); // Notas del procedimiento
            $table->text('complications')->nullable(); // Complicaciones
            $table->text('follow_up_instructions')->nullable(); // Instrucciones de seguimiento
            $table->boolean('requires_follow_up')->default(false); // Requiere seguimiento
            $table->date('next_appointment_date')->nullable(); // Próxima cita
            $table->foreignId('performed_by')->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('treatments');
    }
};
