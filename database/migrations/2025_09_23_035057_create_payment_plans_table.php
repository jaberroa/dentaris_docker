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
        Schema::create('payment_plans', function (Blueprint $table) {
            $table->id();
            $table->string('plan_name'); // Nombre del plan
            $table->text('description')->nullable(); // Descripción
            $table->enum('type', ['installment', 'subscription', 'financing', 'custom']); // Tipo de plan
            $table->integer('installments')->default(1); // Número de cuotas
            $table->enum('frequency', ['daily', 'weekly', 'biweekly', 'monthly', 'quarterly', 'yearly'])->default('monthly'); // Frecuencia
            $table->decimal('interest_rate', 5, 2)->default(0); // Tasa de interés
            $table->decimal('down_payment_percentage', 5, 2)->default(0); // Porcentaje de enganche
            $table->decimal('minimum_amount', 10, 2)->default(0); // Monto mínimo
            $table->decimal('maximum_amount', 10, 2)->nullable(); // Monto máximo
            $table->integer('grace_period_days')->default(0); // Días de gracia
            $table->boolean('requires_credit_check')->default(false); // Requiere verificación de crédito
            $table->boolean('is_active')->default(true); // Activo
            $table->text('terms_conditions')->nullable(); // Términos y condiciones
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_plans');
    }
};
