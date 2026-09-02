<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clinic_membership_roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_membership_id')
                ->constrained()
                ->restrictOnDelete();
            $table->foreignId('role_id')->constrained()->restrictOnDelete();
            $table->timestamps();

            $table->unique(['clinic_membership_id', 'role_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clinic_membership_roles');
    }
};
