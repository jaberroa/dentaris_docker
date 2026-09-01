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
        Schema::create('treatment_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->onDelete('cascade');
            $table->foreignId('staff_id')->constrained()->onDelete('cascade');
            $table->string('plan_name'); // Nombre del plan de tratamiento
            $table->text('description')->nullable(); // Descripción del plan
            $table->enum('status', ['draft', 'active', 'completed', 'cancelled'])->default('draft');
            $table->date('start_date')->nullable(); // Fecha de inicio
            $table->date('end_date')->nullable(); // Fecha de finalización estimada
            $table->integer('total_sessions')->default(0); // Total de sesiones
            $table->decimal('total_cost', 10, 2)->default(0); // Costo total
            $table->text('notes')->nullable(); // Notas adicionales
            $table->boolean('is_urgent')->default(false); // Plan urgente
            $table->boolean('requires_approval')->default(false); // Requiere aprobación
            $table->timestamp('approved_at')->nullable(); // Fecha de aprobación
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('treatment_plans');
    }
};
