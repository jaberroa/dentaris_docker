<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inventory_locations', function (Blueprint $table) {
            $table->foreignId('clinic_id')->nullable()->after('id')->constrained()->restrictOnDelete();
            $table->index(['clinic_id', 'is_active'], 'inv_locations_clinic_active_idx');
        });

        Schema::table('inventory', function (Blueprint $table) {
            $table->foreignId('clinic_id')->nullable()->after('id')->constrained()->restrictOnDelete();
            $table->index(['clinic_id', 'product_id'], 'inventory_clinic_product_idx');
            $table->index(['clinic_id', 'inventory_location_id'], 'inventory_clinic_location_idx');
        });

        Schema::table('inventory_movements', function (Blueprint $table) {
            $table->foreignId('clinic_id')->nullable()->after('id')->constrained()->restrictOnDelete();
            $table->index(['clinic_id', 'inventory_id', 'created_at'], 'inv_movements_clinic_inventory_idx');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->foreignId('clinic_id')->nullable()->after('id')->constrained()->restrictOnDelete();
            $table->index(['clinic_id', 'invoice_date', 'status'], 'invoices_clinic_date_status_idx');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->foreignId('clinic_id')->nullable()->after('id')->constrained()->restrictOnDelete();
            $table->index(['clinic_id', 'payment_date', 'status'], 'payments_clinic_date_status_idx');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex('payments_clinic_date_status_idx');
            $table->dropConstrainedForeignId('clinic_id');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropIndex('invoices_clinic_date_status_idx');
            $table->dropConstrainedForeignId('clinic_id');
        });

        Schema::table('inventory_movements', function (Blueprint $table) {
            $table->dropIndex('inv_movements_clinic_inventory_idx');
            $table->dropConstrainedForeignId('clinic_id');
        });

        Schema::table('inventory', function (Blueprint $table) {
            $table->dropIndex('inventory_clinic_product_idx');
            $table->dropIndex('inventory_clinic_location_idx');
            $table->dropConstrainedForeignId('clinic_id');
        });

        Schema::table('inventory_locations', function (Blueprint $table) {
            $table->dropIndex('inv_locations_clinic_active_idx');
            $table->dropConstrainedForeignId('clinic_id');
        });
    }
};
