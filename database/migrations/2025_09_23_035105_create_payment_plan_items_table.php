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
        Schema::create('payment_plan_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_plan_id')->constrained()->onDelete('cascade');
            $table->foreignId('invoice_id')->constrained()->onDelete('cascade');
            $table->integer('installment_number'); // Número de cuota
            $table->decimal('amount', 10, 2); // Monto de la cuota
            $table->date('due_date'); // Fecha de vencimiento
            $table->date('paid_date')->nullable(); // Fecha de pago
            $table->enum('status', ['pending', 'paid', 'overdue', 'cancelled'])->default('pending');
            $table->decimal('interest_amount', 10, 2)->default(0); // Monto de interés
            $table->decimal('late_fee', 10, 2)->default(0); // Recargo por mora
            $table->text('notes')->nullable(); // Notas
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_plan_items');
    }
};
