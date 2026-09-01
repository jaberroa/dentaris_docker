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
        Schema::create('security_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('event_type'); // login, logout, failed_login, password_change, 2fa_enabled, etc.
            $table->string('event_description');
            $table->string('ip_address');
            $table->string('user_agent')->nullable();
            $table->string('session_id')->nullable();
            $table->json('metadata')->nullable(); // Additional event data
            $table->string('risk_level')->default('low'); // low, medium, high, critical
            $table->boolean('is_suspicious')->default(false);
            $table->string('location')->nullable(); // Country, city if available
            $table->string('device_fingerprint')->nullable();
            $table->timestamp('event_time');
            $table->timestamps();

            $table->index(['user_id', 'event_time']);
            $table->index(['event_type', 'event_time']);
            $table->index(['ip_address', 'event_time']);
            $table->index(['risk_level', 'event_time']);
            $table->index(['is_suspicious', 'event_time']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('security_audit_logs');
    }
};