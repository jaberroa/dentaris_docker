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
        Schema::create('patient_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->onDelete('cascade');
            $table->string('name'); // Nombre del documento
            $table->string('type'); // Tipo: identificación, seguro, receta, etc.
            $table->string('file_path'); // Ruta del archivo
            $table->string('file_name'); // Nombre original del archivo
            $table->string('mime_type'); // Tipo MIME
            $table->integer('file_size'); // Tamaño en bytes
            $table->text('description')->nullable();
            $table->date('expiry_date')->nullable(); // Para documentos con vencimiento
            $table->boolean('is_verified')->default(false);
            $table->foreignId('uploaded_by')->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patient_documents');
    }
};
