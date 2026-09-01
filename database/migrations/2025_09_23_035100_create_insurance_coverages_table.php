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
        Schema::create('insurance_coverages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('insurance_id')->constrained()->onDelete('cascade');
            $table->string('coverage_name'); // Nombre de la cobertura
            $table->text('description')->nullable(); // Descripción
            $table->enum('category', ['preventive', 'restorative', 'surgical', 'orthodontic', 'prosthetic', 'emergency']); // Categoría
            $table->decimal('coverage_percentage', 5, 2)->default(0); // Porcentaje de cobertura
            $table->decimal('maximum_amount', 10, 2)->nullable(); // Monto máximo
            $table->decimal('deductible_amount', 10, 2)->default(0); // Monto del deducible
            $table->integer('annual_limit')->nullable(); // Límite anual
            $table->integer('lifetime_limit')->nullable(); // Límite de por vida
            $table->boolean('requires_authorization')->default(false); // Requiere autorización
            $table->integer('authorization_days')->default(0); // Días para autorización
            $table->text('exclusions')->nullable(); // Exclusiones
            $table->text('notes')->nullable(); // Notas
            $table->boolean('is_active')->default(true); // Activo
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('insurance_coverages');
    }
};
