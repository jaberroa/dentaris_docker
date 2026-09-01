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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique(); // Número de factura
            $table->foreignId('patient_id')->constrained()->onDelete('cascade');
            $table->foreignId('staff_id')->constrained()->onDelete('cascade');
            $table->foreignId('appointment_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('treatment_plan_id')->nullable()->constrained()->onDelete('set null');
            $table->date('invoice_date'); // Fecha de la factura
            $table->date('due_date')->nullable(); // Fecha de vencimiento
            $table->enum('status', ['draft', 'sent', 'paid', 'overdue', 'cancelled'])->default('draft');
            $table->decimal('subtotal', 10, 2)->default(0); // Subtotal
            $table->decimal('tax_rate', 5, 2)->default(0); // Tasa de impuestos
            $table->decimal('tax_amount', 10, 2)->default(0); // Monto de impuestos
            $table->decimal('discount_amount', 10, 2)->default(0); // Descuento
            $table->decimal('total_amount', 10, 2)->default(0); // Total
            $table->decimal('paid_amount', 10, 2)->default(0); // Monto pagado
            $table->decimal('balance_due', 10, 2)->default(0); // Saldo pendiente
            $table->text('notes')->nullable(); // Notas de la factura
            $table->text('payment_terms')->nullable(); // Términos de pago
            $table->boolean('is_recurring')->default(false); // Es factura recurrente
            $table->string('recurring_frequency')->nullable(); // Frecuencia de recurrencia
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
