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
        Schema::create('patient_insurances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->onDelete('cascade');
            $table->foreignId('insurance_id')->constrained()->onDelete('cascade');
            $table->string('policy_number'); // Número de póliza
            $table->string('group_number')->nullable(); // Número de grupo
            $table->string('member_id'); // ID del miembro
            $table->date('effective_date'); // Fecha de vigencia
            $table->date('expiry_date')->nullable(); // Fecha de vencimiento
            $table->string('primary_holder_name')->nullable(); // Nombre del titular principal
            $table->string('relationship')->nullable(); // Relación con el titular
            $table->decimal('coverage_percentage', 5, 2)->default(0); // Porcentaje de cobertura
            $table->decimal('deductible_amount', 10, 2)->default(0); // Monto del deducible
            $table->decimal('annual_limit', 10, 2)->nullable(); // Límite anual
            $table->decimal('lifetime_limit', 10, 2)->nullable(); // Límite de por vida
            $table->boolean('is_primary')->default(false); // Es seguro primario
            $table->boolean('is_active')->default(true); // Activo
            $table->text('notes')->nullable(); // Notas
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patient_insurances');
    }
};
