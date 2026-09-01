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
        Schema::create('inventory', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->integer('current_stock')->default(0); // Stock actual
            $table->integer('reserved_stock')->default(0); // Stock reservado
            $table->integer('available_stock')->default(0); // Stock disponible
            $table->decimal('average_cost', 10, 2)->default(0); // Costo promedio
            $table->date('last_restocked')->nullable(); // Última reposición
            $table->date('last_used')->nullable(); // Último uso
            $table->text('location')->nullable(); // Ubicación en almacén
            $table->text('notes')->nullable(); // Notas
            $table->boolean('low_stock_alert')->default(false); // Alerta de bajo stock
            $table->boolean('out_of_stock_alert')->default(false); // Alerta de agotado
            $table->boolean('expiry_alert')->default(false); // Alerta de vencimiento
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory');
    }
};
