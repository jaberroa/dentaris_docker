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
        Schema::create('lab_work_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lab_work_id')->constrained()->onDelete('cascade');
            $table->foreignId('prosthesis_id')->constrained()->onDelete('cascade');
            $table->integer('quantity')->default(1); // Cantidad
            $table->text('specifications')->nullable(); // Especificaciones del item
            $table->text('notes')->nullable(); // Notas del item
            $table->decimal('unit_cost', 10, 2)->nullable(); // Costo unitario
            $table->decimal('total_cost', 10, 2)->nullable(); // Costo total
            $table->enum('status', ['pending', 'in_progress', 'completed', 'cancelled'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lab_work_items');
    }
};
