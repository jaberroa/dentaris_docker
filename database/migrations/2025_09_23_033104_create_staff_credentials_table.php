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
        Schema::create('staff_credentials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')->constrained()->onDelete('cascade');
            $table->string('credential_type'); // Tipo: cédula, certificado, diploma, etc.
            $table->string('credential_name'); // Nombre del documento
            $table->string('issuing_authority'); // Autoridad emisora
            $table->string('credential_number')->nullable(); // Número del documento
            $table->date('issue_date')->nullable(); // Fecha de emisión
            $table->date('expiry_date')->nullable(); // Fecha de vencimiento
            $table->string('file_path')->nullable(); // Ruta del archivo
            $table->text('description')->nullable(); // Descripción adicional
            $table->boolean('is_verified')->default(false); // Verificado por admin
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staff_credentials');
    }
};
