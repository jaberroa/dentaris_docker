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
        Schema::create('purchase_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->integer('quantity_ordered'); // Cantidad ordenada
            $table->integer('quantity_received')->default(0); // Cantidad recibida
            $table->decimal('unit_cost', 10, 2); // Costo unitario
            $table->decimal('total_cost', 10, 2); // Costo total
            $table->date('expiry_date')->nullable(); // Fecha de vencimiento
            $table->text('notes')->nullable(); // Notas del item
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_items');
    }
};
