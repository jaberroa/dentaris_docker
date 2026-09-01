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
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('supplier_code')->unique(); // Código del proveedor
            $table->string('company_name'); // Nombre de la empresa
            $table->string('contact_name')->nullable(); // Nombre del contacto
            $table->string('email')->nullable(); // Email
            $table->string('phone')->nullable(); // Teléfono
            $table->string('address')->nullable(); // Dirección
            $table->string('city')->nullable(); // Ciudad
            $table->string('state')->nullable(); // Estado
            $table->string('postal_code')->nullable(); // Código postal
            $table->string('country')->default('México'); // País
            $table->string('tax_id')->nullable(); // RFC o ID fiscal
            $table->text('notes')->nullable(); // Notas
            $table->enum('payment_terms', ['net_15', 'net_30', 'net_45', 'net_60', 'cash_on_delivery'])->default('net_30');
            $table->decimal('credit_limit', 10, 2)->nullable(); // Límite de crédito
            $table->boolean('is_active')->default(true); // Activo
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('suppliers');
    }
};
