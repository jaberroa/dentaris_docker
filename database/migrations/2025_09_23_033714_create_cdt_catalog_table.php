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
        Schema::create('cdt_catalog', function (Blueprint $table) {
            $table->id();
            $table->string('cdt_code')->unique(); // Código CDT (ej: D0120)
            $table->string('category'); // Categoría (Preventivo, Restaurativo, etc.)
            $table->string('subcategory')->nullable(); // Subcategoría
            $table->string('procedure_name'); // Nombre del procedimiento
            $table->text('description')->nullable(); // Descripción detallada
            $table->text('clinical_notes')->nullable(); // Notas clínicas
            $table->decimal('base_price', 10, 2)->nullable(); // Precio base
            $table->integer('estimated_duration')->nullable(); // Duración estimada en minutos
            $table->enum('difficulty_level', ['basic', 'intermediate', 'advanced', 'expert'])->default('basic');
            $table->json('required_materials')->nullable(); // Materiales requeridos
            $table->json('contraindications')->nullable(); // Contraindicaciones
            $table->boolean('requires_anesthesia')->default(false); // Requiere anestesia
            $table->boolean('is_surgical')->default(false); // Es procedimiento quirúrgico
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cdt_catalog');
    }
};
