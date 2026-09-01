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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('product_code')->unique(); // Código del producto
            $table->string('name'); // Nombre del producto
            $table->text('description')->nullable(); // Descripción
            $table->string('category'); // Categoría (materiales, equipos, medicamentos, etc.)
            $table->string('subcategory')->nullable(); // Subcategoría
            $table->string('unit_of_measure'); // Unidad de medida (piezas, kg, litros, etc.)
            $table->decimal('cost_price', 10, 2)->nullable(); // Precio de costo
            $table->decimal('selling_price', 10, 2)->nullable(); // Precio de venta
            $table->integer('minimum_stock')->default(0); // Stock mínimo
            $table->integer('maximum_stock')->nullable(); // Stock máximo
            $table->string('barcode')->nullable(); // Código de barras
            $table->string('brand')->nullable(); // Marca
            $table->string('model')->nullable(); // Modelo
            $table->date('expiry_date')->nullable(); // Fecha de vencimiento
            $table->text('storage_conditions')->nullable(); // Condiciones de almacenamiento
            $table->text('usage_instructions')->nullable(); // Instrucciones de uso
            $table->boolean('requires_prescription')->default(false); // Requiere receta
            $table->boolean('is_controlled')->default(false); // Es controlado
            $table->boolean('is_active')->default(true); // Activo
            $table->foreignId('primary_supplier_id')->nullable()->constrained('suppliers');
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
