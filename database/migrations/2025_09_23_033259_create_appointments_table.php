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
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->string('appointment_code')->unique(); // Código único de la cita
            $table->foreignId('patient_id')->constrained()->onDelete('cascade');
            $table->foreignId('staff_id')->constrained()->onDelete('cascade');
            $table->foreignId('appointment_status_id')->constrained()->onDelete('restrict');
            $table->date('appointment_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->integer('duration')->default(30); // Duración en minutos
            $table->string('type'); // Tipo: consulta, limpieza, tratamiento, etc.
            $table->text('reason')->nullable(); // Motivo de la cita
            $table->text('notes')->nullable(); // Notas adicionales
            $table->text('treatment_plan')->nullable(); // Plan de tratamiento
            $table->decimal('estimated_cost', 10, 2)->nullable(); // Costo estimado
            $table->boolean('is_urgent')->default(false); // Cita urgente
            $table->boolean('is_follow_up')->default(false); // Cita de seguimiento
            $table->foreignId('parent_appointment_id')->nullable()->constrained('appointments')->onDelete('set null'); // Cita padre para seguimientos
            $table->timestamp('confirmed_at')->nullable(); // Fecha de confirmación
            $table->timestamp('cancelled_at')->nullable(); // Fecha de cancelación
            $table->text('cancellation_reason')->nullable(); // Razón de cancelación
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
