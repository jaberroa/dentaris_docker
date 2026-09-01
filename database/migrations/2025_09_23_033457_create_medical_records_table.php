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
        Schema::create('medical_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->onDelete('cascade');
            $table->foreignId('appointment_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('staff_id')->constrained()->onDelete('cascade');
            $table->string('record_type'); // consulta, tratamiento, seguimiento, urgencia
            $table->text('chief_complaint'); // Motivo de consulta
            $table->text('present_illness'); // Enfermedad actual
            $table->text('medical_history'); // Antecedentes médicos
            $table->text('dental_history'); // Antecedentes dentales
            $table->text('family_history'); // Antecedentes familiares
            $table->text('social_history'); // Antecedentes sociales
            $table->text('clinical_examination'); // Examen clínico
            $table->text('vital_signs')->nullable(); // Signos vitales
            $table->text('oral_examination'); // Examen oral
            $table->text('diagnostic_impression'); // Impresión diagnóstica
            $table->text('treatment_plan'); // Plan de tratamiento
            $table->text('recommendations'); // Recomendaciones
            $table->text('notes')->nullable(); // Notas adicionales
            $table->boolean('is_confidential')->default(false); // Información confidencial
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medical_records');
    }
};
