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
        Schema::create('medical_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medical_record_id')->constrained()->onDelete('cascade');
            $table->string('image_type'); // radiografia, foto_clinica, escaner, etc.
            $table->string('image_name'); // Nombre descriptivo
            $table->string('file_path'); // Ruta del archivo
            $table->string('file_name'); // Nombre original del archivo
            $table->string('mime_type'); // Tipo MIME
            $table->integer('file_size'); // Tamaño en bytes
            $table->integer('width')->nullable(); // Ancho en píxeles
            $table->integer('height')->nullable(); // Alto en píxeles
            $table->text('description')->nullable(); // Descripción de la imagen
            $table->text('anatomical_location')->nullable(); // Ubicación anatómica
            $table->text('findings')->nullable(); // Hallazgos en la imagen
            $table->boolean('is_processed')->default(false); // Imagen procesada/analizada
            $table->json('metadata')->nullable(); // Metadatos adicionales
            $table->foreignId('uploaded_by')->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medical_images');
    }
};
