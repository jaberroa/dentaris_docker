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
        Schema::create('appointment_status_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('appointment_id');
            $table->unsignedBigInteger('patient_id');
            $table->unsignedBigInteger('staff_id');
            $table->unsignedBigInteger('user_id');
            $table->string('user_role');
            $table->string('old_status');
            $table->string('new_status');
            $table->boolean('success');
            $table->text('reason');
            $table->string('ip_address', 45);
            $table->text('user_agent');
            $table->timestamps();

            $table->index(['appointment_id', 'created_at']);
            $table->index(['user_id', 'created_at']);
            $table->index(['success', 'created_at']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointment_status_logs');
    }
};
