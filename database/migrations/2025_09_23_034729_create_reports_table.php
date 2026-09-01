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
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->string('report_name'); // Nombre del reporte
            $table->string('report_type'); // Tipo de reporte (daily, monthly, yearly, custom)
            $table->text('description')->nullable(); // Descripción del reporte
            $table->date('report_date'); // Fecha del reporte
            $table->date('start_date')->nullable(); // Fecha de inicio del período
            $table->date('end_date')->nullable(); // Fecha de fin del período
            $table->json('filters')->nullable(); // Filtros aplicados
            $table->json('data')->nullable(); // Datos del reporte
            $table->json('metrics')->nullable(); // Métricas calculadas
            $table->enum('status', ['generating', 'completed', 'failed'])->default('generating');
            $table->string('file_path')->nullable(); // Ruta del archivo generado
            $table->string('file_format')->nullable(); // Formato del archivo (pdf, excel, csv)
            $table->integer('file_size')->nullable(); // Tamaño del archivo
            $table->text('notes')->nullable(); // Notas del reporte
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
