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
        Schema::create('accounts_receivable', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->onDelete('cascade');
            $table->foreignId('invoice_id')->constrained()->onDelete('cascade');
            $table->decimal('original_amount', 10, 2); // Monto original
            $table->decimal('paid_amount', 10, 2)->default(0); // Monto pagado
            $table->decimal('balance_due', 10, 2); // Saldo pendiente
            $table->date('due_date'); // Fecha de vencimiento
            $table->enum('status', ['current', 'overdue', 'paid', 'written_off'])->default('current');
            $table->integer('days_overdue')->default(0); // Días de atraso
            $table->decimal('interest_rate', 5, 2)->default(0); // Tasa de interés
            $table->decimal('interest_amount', 10, 2)->default(0); // Monto de interés
            $table->text('notes')->nullable(); // Notas
            $table->date('last_payment_date')->nullable(); // Fecha del último pago
            $table->decimal('last_payment_amount', 10, 2)->nullable(); // Monto del último pago
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accounts_receivable');
    }
};
