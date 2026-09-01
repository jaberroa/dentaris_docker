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
        Schema::create('feedback', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('staff_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('appointment_id')->nullable()->constrained()->onDelete('set null');
            $table->enum('type', ['complaint', 'compliment', 'suggestion', 'general']); // Tipo de feedback
            $table->enum('category', ['service', 'treatment', 'staff', 'facilities', 'waiting_time', 'cost', 'other']); // Categoría
            $table->text('message'); // Mensaje del feedback
            $table->integer('rating')->nullable(); // Calificación (1-5)
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium'); // Prioridad
            $table->enum('status', ['new', 'in_review', 'responded', 'resolved', 'closed'])->default('new'); // Estado
            $table->text('admin_response')->nullable(); // Respuesta del administrador
            $table->timestamp('responded_at')->nullable(); // Fecha de respuesta
            $table->foreignId('responded_by')->nullable()->constrained('users');
            $table->boolean('is_anonymous')->default(false); // Es anónimo
            $table->boolean('is_public')->default(false); // Es público
            $table->text('notes')->nullable(); // Notas internas
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feedback');
    }
};
