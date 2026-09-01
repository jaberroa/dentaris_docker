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
        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->onDelete('cascade');
            $table->foreignId('cdt_catalog_id')->nullable()->constrained('cdt_catalog')->onDelete('set null');
            $table->integer('sequence_order')->default(1); // Orden de secuencia
            $table->string('item_name'); // Nombre del item
            $table->text('description')->nullable(); // Descripción del item
            $table->integer('quantity')->default(1); // Cantidad
            $table->decimal('unit_price', 10, 2); // Precio unitario
            $table->decimal('total_price', 10, 2); // Precio total
            $table->decimal('tax_rate', 5, 2)->default(0); // Tasa de impuestos del item
            $table->decimal('tax_amount', 10, 2)->default(0); // Monto de impuestos del item
            $table->text('notes')->nullable(); // Notas del item
            $table->boolean('is_taxable')->default(true); // Es gravable
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_items');
    }
};
