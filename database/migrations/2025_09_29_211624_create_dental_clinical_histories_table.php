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
        Schema::create('dental_clinical_histories', function (Blueprint $table) {
            $table->id();

            $table->foreignId('dental_treatment_plan_id')
                ->constrained('dental_treatment_plans')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreignId('dental_procedure_id')
                ->nullable()
                ->constrained('dental_procedures')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->foreignId('staff_id')
                ->nullable()
                ->constrained('staff')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->enum('action_type', ['created', 'updated', 'status_changed', 'completed', 'cancelled', 'note_added']);
            $table->string('title');

            $table->json('old_value')->nullable();
            $table->json('new_value')->nullable();

            $table->text('notes')->nullable();

            $table->timestamp('action_at')->useCurrent();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dental_clinical_histories');
    }
};
