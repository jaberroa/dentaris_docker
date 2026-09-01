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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('payment_number')->unique(); // Número de pago
            $table->foreignId('invoice_id')->constrained()->onDelete('cascade');
            $table->foreignId('patient_id')->constrained()->onDelete('cascade');
            $table->date('payment_date'); // Fecha del pago
            $table->decimal('amount', 10, 2); // Monto del pago
            $table->enum('payment_method', ['cash', 'card', 'transfer', 'check', 'other']); // Método de pago
            $table->string('reference_number')->nullable(); // Número de referencia
            $table->text('notes')->nullable(); // Notas del pago
            $table->enum('status', ['pending', 'completed', 'failed', 'cancelled'])->default('pending');
            $table->string('transaction_id')->nullable(); // ID de transacción
            $table->json('payment_details')->nullable(); // Detalles del pago
            $table->foreignId('processed_by')->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
