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
        Schema::create('quotes', function (Blueprint $table) {
            $table->id();
            $table->string('quote_number')->unique(); // Número de presupuesto
            $table->foreignId('patient_id')->constrained()->onDelete('cascade');
            $table->foreignId('staff_id')->constrained()->onDelete('cascade');
            $table->foreignId('treatment_plan_id')->nullable()->constrained()->onDelete('set null');
            $table->date('quote_date'); // Fecha del presupuesto
            $table->date('valid_until')->nullable(); // Válido hasta
            $table->enum('status', ['draft', 'sent', 'accepted', 'rejected', 'expired'])->default('draft');
            $table->decimal('subtotal', 10, 2)->default(0); // Subtotal
            $table->decimal('tax_rate', 5, 2)->default(0); // Tasa de impuestos
            $table->decimal('tax_amount', 10, 2)->default(0); // Monto de impuestos
            $table->decimal('discount_amount', 10, 2)->default(0); // Descuento
            $table->decimal('total_amount', 10, 2)->default(0); // Total
            $table->text('notes')->nullable(); // Notas del presupuesto
            $table->text('terms_conditions')->nullable(); // Términos y condiciones
            $table->boolean('includes_anesthesia')->default(false); // Incluye anestesia
            $table->boolean('requires_deposit')->default(false); // Requiere anticipo
            $table->decimal('deposit_amount', 10, 2)->nullable(); // Monto del anticipo
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quotes');
    }
};
