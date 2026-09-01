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
        Schema::create('medical_diagnoses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medical_record_id')->constrained()->onDelete('cascade');
            $table->string('diagnosis_code')->nullable(); // Código CIE-10
            $table->string('diagnosis_name'); // Nombre del diagnóstico
            $table->text('description')->nullable(); // Descripción detallada
            $table->enum('type', ['primary', 'secondary', 'differential'])->default('primary');
            $table->enum('status', ['active', 'resolved', 'chronic', 'recurring'])->default('active');
            $table->date('diagnosis_date'); // Fecha del diagnóstico
            $table->date('resolved_date')->nullable(); // Fecha de resolución
            $table->text('treatment_notes')->nullable(); // Notas del tratamiento
            $table->text('follow_up_notes')->nullable(); // Notas de seguimiento
            $table->boolean('is_confirmed')->default(false); // Diagnóstico confirmado
            $table->foreignId('diagnosed_by')->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medical_diagnoses');
    }
};
