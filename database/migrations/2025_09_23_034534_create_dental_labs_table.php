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
        Schema::create('dental_labs', function (Blueprint $table) {
            $table->id();
            $table->string('lab_code')->unique(); // Código del laboratorio
            $table->string('lab_name'); // Nombre del laboratorio
            $table->string('contact_name')->nullable(); // Nombre del contacto
            $table->string('email')->nullable(); // Email
            $table->string('phone')->nullable(); // Teléfono
            $table->string('address')->nullable(); // Dirección
            $table->string('city')->nullable(); // Ciudad
            $table->string('state')->nullable(); // Estado
            $table->string('postal_code')->nullable(); // Código postal
            $table->string('country')->default('México'); // País
            $table->text('specialties')->nullable(); // Especialidades
            $table->text('services')->nullable(); // Servicios ofrecidos
            $table->decimal('average_turnaround_days', 5, 2)->nullable(); // Tiempo promedio de entrega
            $table->decimal('quality_rating', 3, 2)->nullable(); // Calificación de calidad
            $table->text('notes')->nullable(); // Notas
            $table->boolean('is_active')->default(true); // Activo
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dental_labs');
    }
};
