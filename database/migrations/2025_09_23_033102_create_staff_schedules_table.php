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
        Schema::create('staff_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')->constrained()->onDelete('cascade');
            $table->enum('day_of_week', ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday']);
            $table->time('start_time');
            $table->time('end_time');
            $table->integer('break_duration')->default(0); // Duración del descanso en minutos
            $table->time('break_start')->nullable(); // Hora de inicio del descanso
            $table->integer('appointment_duration')->default(30); // Duración de citas en minutos
            $table->boolean('is_available')->default(true);
            $table->date('effective_from')->nullable(); // Fecha desde cuando es efectivo
            $table->date('effective_until')->nullable(); // Fecha hasta cuando es efectivo
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staff_schedules');
    }
};
