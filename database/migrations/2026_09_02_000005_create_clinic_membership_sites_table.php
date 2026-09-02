<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clinic_membership_sites', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('clinic_membership_id');
            $table->unsignedBigInteger('clinic_site_id');
            $table->unsignedBigInteger('clinic_id');
            $table->timestamps();

            $table->unique(
                ['clinic_membership_id', 'clinic_site_id'],
                'clinic_membership_sites_membership_site_unique'
            );
            $table->index(
                ['clinic_membership_id', 'clinic_id'],
                'clinic_membership_sites_membership_clinic_index'
            );
            $table->index(
                ['clinic_site_id', 'clinic_id'],
                'clinic_membership_sites_site_clinic_index'
            );

            $table->foreign(
                ['clinic_membership_id', 'clinic_id'],
                'clinic_membership_sites_membership_clinic_foreign'
            )->references(['id', 'clinic_id'])->on('clinic_memberships')->restrictOnDelete();
            $table->foreign(
                ['clinic_site_id', 'clinic_id'],
                'clinic_membership_sites_site_clinic_foreign'
            )->references(['id', 'clinic_id'])->on('clinic_sites')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clinic_membership_sites');
    }
};
