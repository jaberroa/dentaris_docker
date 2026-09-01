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
        Schema::create('report_templates', function (Blueprint $table) {
            $table->id();
            $table->string('template_name'); // Nombre de la plantilla
            $table->string('template_code')->unique(); // Código de la plantilla
            $table->text('description')->nullable(); // Descripción
            $table->enum('report_type', ['daily', 'monthly', 'yearly', 'custom']); // Tipo de reporte
            $table->json('template_config')->nullable(); // Configuración de la plantilla
            $table->json('data_sources')->nullable(); // Fuentes de datos
            $table->json('filters')->nullable(); // Filtros por defecto
            $table->json('charts')->nullable(); // Configuración de gráficos
            $table->text('sql_query')->nullable(); // Consulta SQL personalizada
            $table->boolean('is_active')->default(true); // Activo
            $table->boolean('is_system')->default(false); // Es plantilla del sistema
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('report_templates');
    }
};
