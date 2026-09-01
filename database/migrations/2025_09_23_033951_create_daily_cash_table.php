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
        Schema::create('daily_cash', function (Blueprint $table) {
            $table->id();
            $table->date('cash_date'); // Fecha de la caja
            $table->decimal('opening_balance', 10, 2)->default(0); // Saldo inicial
            $table->decimal('cash_sales', 10, 2)->default(0); // Ventas en efectivo
            $table->decimal('card_sales', 10, 2)->default(0); // Ventas con tarjeta
            $table->decimal('transfer_sales', 10, 2)->default(0); // Ventas por transferencia
            $table->decimal('total_sales', 10, 2)->default(0); // Total de ventas
            $table->decimal('cash_expenses', 10, 2)->default(0); // Gastos en efectivo
            $table->decimal('cash_withdrawals', 10, 2)->default(0); // Retiros de efectivo
            $table->decimal('expected_balance', 10, 2)->default(0); // Saldo esperado
            $table->decimal('actual_balance', 10, 2)->default(0); // Saldo real
            $table->decimal('difference', 10, 2)->default(0); // Diferencia
            $table->text('notes')->nullable(); // Notas de la caja
            $table->enum('status', ['open', 'closed'])->default('open'); // Estado de la caja
            $table->timestamp('opened_at')->nullable(); // Hora de apertura
            $table->timestamp('closed_at')->nullable(); // Hora de cierre
            $table->foreignId('opened_by')->constrained('users');
            $table->foreignId('closed_by')->nullable()->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_cash');
    }
};
