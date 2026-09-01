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
        Schema::create('patients', function (Blueprint $table) {
            $table->id();
            $table->string('patient_code')->unique(); // Código único del paciente
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('phone_secondary')->nullable();
            $table->date('birth_date');
            $table->enum('gender', ['male', 'female', 'other']);
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('country')->default('México');
            
            // Información médica
            $table->text('medical_history')->nullable();
            $table->text('dental_history')->nullable();
            $table->text('allergies')->nullable();
            $table->text('medications')->nullable();
            $table->text('family_history')->nullable();
            $table->text('social_history')->nullable();
            $table->string('blood_type')->nullable();
            $table->string('occupation')->nullable();
            $table->string('marital_status')->nullable();
            
            // Contactos de emergencia
            $table->text('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone')->nullable();
            $table->string('emergency_contact_relationship')->nullable();
            $table->text('emergency_contact_address')->nullable();
            
            // Información adicional
            $table->text('notes')->nullable();
            $table->text('preferences')->nullable(); // Preferencias del paciente
            $table->boolean('consent_marketing')->default(false); // Consentimiento para marketing
            $table->boolean('consent_data_processing')->default(true); // Consentimiento para procesamiento de datos
            
            // Información del sistema
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
};
