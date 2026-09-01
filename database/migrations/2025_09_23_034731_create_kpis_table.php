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
        Schema::create('kpis', function (Blueprint $table) {
            $table->id();
            $table->string('kpi_name'); // Nombre del KPI
            $table->string('kpi_code')->unique(); // Código del KPI
            $table->text('description')->nullable(); // Descripción del KPI
            $table->enum('category', ['financial', 'operational', 'clinical', 'patient_satisfaction', 'staff_performance']); // Categoría
            $table->string('unit_of_measure')->nullable(); // Unidad de medida
            $table->enum('calculation_method', ['sum', 'average', 'count', 'percentage', 'ratio', 'custom']); // Método de cálculo
            $table->text('calculation_formula')->nullable(); // Fórmula de cálculo
            $table->decimal('target_value', 10, 2)->nullable(); // Valor objetivo
            $table->decimal('current_value', 10, 2)->nullable(); // Valor actual
            $table->enum('trend', ['up', 'down', 'stable'])->nullable(); // Tendencia
            $table->boolean('is_active')->default(true); // Activo
            $table->integer('sort_order')->default(0); // Orden de visualización
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kpis');
    }
};
