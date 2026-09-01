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
        Schema::create('insurances', function (Blueprint $table) {
            $table->id();
            $table->string('insurance_code')->unique(); // Código de la aseguradora
            $table->string('company_name'); // Nombre de la compañía
            $table->string('contact_name')->nullable(); // Nombre del contacto
            $table->string('email')->nullable(); // Email
            $table->string('phone')->nullable(); // Teléfono
            $table->string('address')->nullable(); // Dirección
            $table->string('city')->nullable(); // Ciudad
            $table->string('state')->nullable(); // Estado
            $table->string('postal_code')->nullable(); // Código postal
            $table->string('country')->default('México'); // País
            $table->string('tax_id')->nullable(); // RFC o ID fiscal
            $table->text('description')->nullable(); // Descripción
            $table->enum('type', ['private', 'public', 'social_security', 'other']); // Tipo de seguro
            $table->decimal('coverage_percentage', 5, 2)->default(0); // Porcentaje de cobertura
            $table->decimal('deductible_amount', 10, 2)->default(0); // Monto del deducible
            $table->text('coverage_details')->nullable(); // Detalles de cobertura
            $table->text('exclusions')->nullable(); // Exclusiones
            $table->boolean('requires_authorization')->default(false); // Requiere autorización
            $table->integer('authorization_days')->default(0); // Días para autorización
            $table->text('notes')->nullable(); // Notas
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
        Schema::dropIfExists('insurances');
    }
};
