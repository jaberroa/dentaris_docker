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
        Schema::create('quote_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quote_id')->constrained()->onDelete('cascade');
            $table->foreignId('cdt_catalog_id')->constrained('cdt_catalog')->onDelete('cascade');
            $table->integer('sequence_order')->default(1); // Orden de secuencia
            $table->string('item_name'); // Nombre del item
            $table->text('description')->nullable(); // Descripción del item
            $table->integer('quantity')->default(1); // Cantidad
            $table->decimal('unit_price', 10, 2); // Precio unitario
            $table->decimal('total_price', 10, 2); // Precio total
            $table->integer('estimated_duration')->nullable(); // Duración estimada en minutos
            $table->text('notes')->nullable(); // Notas del item
            $table->boolean('is_optional')->default(false); // Es opcional
            $table->boolean('requires_anesthesia')->default(false); // Requiere anestesia
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quote_items');
    }
};
