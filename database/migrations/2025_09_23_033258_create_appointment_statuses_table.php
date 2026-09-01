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
        Schema::create('appointment_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // scheduled, confirmed, in_progress, completed, cancelled, no_show
            $table->string('display_name'); // Programada, Confirmada, En Progreso, Completada, Cancelada, No Asistió
            $table->string('color')->default('#6c757d'); // Color para el calendario
            $table->string('icon')->nullable(); // Icono para mostrar
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_final')->default(false); // Estado final (completada, cancelada, no_show)
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointment_statuses');
    }
};
