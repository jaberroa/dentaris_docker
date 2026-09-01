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
        Schema::create('dental_odontograms', function (Blueprint $table) {
            $table->id();

            $table->foreignId('dental_treatment_plan_id')
                ->constrained('dental_treatment_plans')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->string('tooth_number', 3);
            $table->enum('tooth_type', ['permanent', 'temporary', 'mixed'])->default('permanent');

            $table->json('surfaces')->nullable(); // estados por superficie
            $table->json('conditions')->nullable(); // condiciones generales del diente
            $table->json('procedures')->nullable(); // procedimientos asociados al diente

            $table->boolean('is_missing')->default(false);
            $table->boolean('needs_attention')->default(false);

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->unique(['dental_treatment_plan_id', 'tooth_number'], 'odonto_plan_tooth_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dental_odontograms');
    }
};
