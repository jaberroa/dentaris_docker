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
        Schema::create('dental_periodontograms', function (Blueprint $table) {
            $table->id();

            $table->foreignId('dental_treatment_plan_id')
                ->constrained('dental_treatment_plans')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->string('tooth_number', 3);

            $table->json('measurement_points')->nullable();
            $table->json('pocket_depth')->nullable();
            $table->json('bleeding')->nullable();

            $table->enum('mobility', ['0', '1', '2', '3'])->default('0');
            $table->decimal('gingival_recession', 5, 2)->default(0);
            $table->decimal('clinical_attachment_loss', 5, 2)->default(0);

            $table->boolean('furcation_involvement')->default(false);
            $table->boolean('suppuration')->default(false);

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->unique(['dental_treatment_plan_id', 'tooth_number'], 'peri_plan_tooth_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dental_periodontograms');
    }
};
