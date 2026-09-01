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
        Schema::create('lab_works', function (Blueprint $table) {
            $table->id();
            $table->string('work_number')->unique(); // Número de trabajo
            $table->foreignId('patient_id')->constrained()->onDelete('cascade');
            $table->foreignId('staff_id')->constrained()->onDelete('cascade');
            $table->foreignId('dental_lab_id')->constrained()->onDelete('cascade');
            $table->foreignId('appointment_id')->nullable()->constrained()->onDelete('set null');
            $table->date('work_date'); // Fecha del trabajo
            $table->date('expected_delivery')->nullable(); // Fecha esperada de entrega
            $table->date('actual_delivery')->nullable(); // Fecha real de entrega
            $table->enum('status', ['pending', 'sent', 'in_progress', 'completed', 'delivered', 'cancelled'])->default('pending');
            $table->text('work_description'); // Descripción del trabajo
            $table->text('specifications')->nullable(); // Especificaciones
            $table->text('notes')->nullable(); // Notas
            $table->decimal('cost', 10, 2)->nullable(); // Costo
            $table->decimal('paid_amount', 10, 2)->default(0); // Monto pagado
            $table->boolean('is_urgent')->default(false); // Es urgente
            $table->boolean('requires_pickup')->default(false); // Requiere recogida
            $table->string('tracking_number')->nullable(); // Número de seguimiento
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lab_works');
    }
};
