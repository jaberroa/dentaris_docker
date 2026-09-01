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
        Schema::create('staff', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('employee_id')->unique(); // ID de empleado
            $table->string('specialty')->nullable(); // Especialidad dental
            $table->string('license_number')->nullable(); // Número de cédula
            $table->date('license_expiry')->nullable(); // Vencimiento de cédula
            $table->string('university')->nullable(); // Universidad donde estudió
            $table->year('graduation_year')->nullable(); // Año de graduación
            $table->text('bio')->nullable(); // Biografía profesional
            $table->string('profile_photo')->nullable(); // Foto de perfil
            $table->decimal('consultation_fee', 8, 2)->nullable(); // Tarifa de consulta
            $table->integer('experience_years')->default(0); // Años de experiencia
            $table->json('languages')->nullable(); // Idiomas que habla
            $table->json('certifications')->nullable(); // Certificaciones
            $table->boolean('is_available')->default(true); // Disponible para citas
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staff');
    }
};
