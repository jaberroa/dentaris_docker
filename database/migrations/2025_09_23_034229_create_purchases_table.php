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
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->string('purchase_number')->unique(); // Número de compra
            $table->foreignId('supplier_id')->constrained()->onDelete('cascade');
            $table->date('purchase_date'); // Fecha de compra
            $table->date('expected_delivery')->nullable(); // Fecha esperada de entrega
            $table->date('actual_delivery')->nullable(); // Fecha real de entrega
            $table->enum('status', ['pending', 'ordered', 'received', 'cancelled'])->default('pending');
            $table->decimal('subtotal', 10, 2)->default(0); // Subtotal
            $table->decimal('tax_rate', 5, 2)->default(0); // Tasa de impuestos
            $table->decimal('tax_amount', 10, 2)->default(0); // Monto de impuestos
            $table->decimal('shipping_cost', 10, 2)->default(0); // Costo de envío
            $table->decimal('discount_amount', 10, 2)->default(0); // Descuento
            $table->decimal('total_amount', 10, 2)->default(0); // Total
            $table->text('notes')->nullable(); // Notas
            $table->string('invoice_number')->nullable(); // Número de factura del proveedor
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
        Schema::dropIfExists('purchases');
    }
};
