<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clinic_memberships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->constrained()->restrictOnDelete();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->string('status', 20)->default('pending');
            $table->timestamp('activated_at')->nullable();
            $table->timestamp('suspended_at')->nullable();
            $table->timestamps();

            $table->unique(['clinic_id', 'user_id']);
            $table->index(['clinic_id', 'status']);
            $table->index(['user_id', 'status']);
            $table->unique(['id', 'clinic_id'], 'clinic_memberships_id_clinic_id_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clinic_memberships');
    }
};
