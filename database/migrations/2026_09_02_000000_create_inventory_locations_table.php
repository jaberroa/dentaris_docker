<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_locations', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name')->unique();
            $table->string('type', 32)->default('storage');
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::table('inventory', function (Blueprint $table) {
            $table->foreignId('inventory_location_id')
                ->nullable()
                ->after('product_id')
                ->constrained('inventory_locations')
                ->nullOnDelete();
            $table->unique(['product_id', 'inventory_location_id'], 'inventory_product_location_unique');
        });
    }

    public function down(): void
    {
        Schema::table('inventory', function (Blueprint $table) {
            $table->dropUnique('inventory_product_location_unique');
            $table->dropConstrainedForeignId('inventory_location_id');
        });

        Schema::dropIfExists('inventory_locations');
    }
};
