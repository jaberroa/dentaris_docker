<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->foreignId('clinic_id')->nullable()->after('id')->constrained()->restrictOnDelete();
            $table->index(['clinic_id', 'is_active'], 'suppliers_clinic_active_idx');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('clinic_id')->nullable()->after('id')->constrained()->restrictOnDelete();
            $table->index(['clinic_id', 'category', 'is_active'], 'products_clinic_category_active_idx');
            $table->index(['clinic_id', 'primary_supplier_id'], 'products_clinic_supplier_idx');
        });

        Schema::table('purchases', function (Blueprint $table) {
            $table->foreignId('clinic_id')->nullable()->after('id')->constrained()->restrictOnDelete();
            $table->index(['clinic_id', 'purchase_date', 'status'], 'purchases_clinic_date_status_idx');
            $table->index(['clinic_id', 'supplier_id'], 'purchases_clinic_supplier_idx');
        });

        Schema::table('quotes', function (Blueprint $table) {
            $table->foreignId('clinic_id')->nullable()->after('id')->constrained()->restrictOnDelete();
            $table->index(['clinic_id', 'quote_date', 'status'], 'quotes_clinic_date_status_idx');
            $table->index(['clinic_id', 'patient_id'], 'quotes_clinic_patient_idx');
        });
    }

    public function down(): void
    {
        Schema::table('quotes', function (Blueprint $table) {
            $table->dropIndex('quotes_clinic_date_status_idx');
            $table->dropIndex('quotes_clinic_patient_idx');
            $table->dropConstrainedForeignId('clinic_id');
        });

        Schema::table('purchases', function (Blueprint $table) {
            $table->dropIndex('purchases_clinic_date_status_idx');
            $table->dropIndex('purchases_clinic_supplier_idx');
            $table->dropConstrainedForeignId('clinic_id');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('products_clinic_category_active_idx');
            $table->dropIndex('products_clinic_supplier_idx');
            $table->dropConstrainedForeignId('clinic_id');
        });

        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropIndex('suppliers_clinic_active_idx');
            $table->dropConstrainedForeignId('clinic_id');
        });
    }
};
