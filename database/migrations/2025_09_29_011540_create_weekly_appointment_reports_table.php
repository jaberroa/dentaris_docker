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
        Schema::create('weekly_appointment_reports', function (Blueprint $table) {
            $table->id();
            $table->date('week_start');
            $table->date('week_end');
            $table->integer('week_number');
            $table->integer('year');
            $table->integer('total_appointments');
            $table->json('status_breakdown');
            $table->json('frequent_changes');
            $table->json('active_users');
            $table->integer('failed_changes');
            $table->json('failure_reasons');
            $table->timestamp('generated_at');
            $table->timestamps();

            $table->index(['year', 'week_number']);
            $table->index('generated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('weekly_appointment_reports');
    }
};
