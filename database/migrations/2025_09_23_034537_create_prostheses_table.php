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
        Schema::create('prostheses', function (Blueprint $table) {
            $table->id();
            $table->string('prosthesis_code')->unique(); // Código de la prótesis
            $table->string('prosthesis_name'); // Nombre de la prótesis
            $table->text('description')->nullable(); // Descripción
            $table->enum('type', ['crown', 'bridge', 'denture', 'implant', 'veneer', 'inlay', 'onlay', 'other']); // Tipo de prótesis
            $table->string('material')->nullable(); // Material
            $table->string('color')->nullable(); // Color
            $table->string('size')->nullable(); // Tamaño
            $table->decimal('cost', 10, 2)->nullable(); // Costo
            $table->integer('estimated_days')->nullable(); // Días estimados de fabricación
            $table->text('specifications')->nullable(); // Especificaciones técnicas
            $table->text('care_instructions')->nullable(); // Instrucciones de cuidado
            $table->boolean('requires_lab')->default(true); // Requiere laboratorio
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
        Schema::dropIfExists('prostheses');
    }
};
