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
        Schema::create('dental_treatment_plans', function (Blueprint $table) {
            $table->id();

            $table->foreignId('patient_id')
                ->constrained('patients')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignId('staff_id')
                ->nullable()
                ->constrained('staff')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->string('plan_code', 20)->unique();
            $table->string('plan_name');

            $table->enum('patient_type', ['adult', 'child', 'mixed'])->default('adult');
            $table->enum('work_schema', ['odontogram', 'periodontogram', 'both'])->default('odontogram');
            $table->enum('status', ['draft', 'active', 'on_hold', 'completed', 'cancelled'])->default('draft');

            $table->unsignedInteger('total_procedures')->default(0);
            $table->unsignedInteger('completed_procedures')->default(0);
            $table->decimal('progress_percentage', 5, 2)->default(0);

            $table->unsignedInteger('priority')->default(2); // 1: Alta, 2: Media, 3: Baja
            $table->boolean('is_urgent')->default(false);

            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();

            $table->decimal('estimated_total_cost', 12, 2)->default(0);
            $table->decimal('actual_total_cost', 12, 2)->default(0);

            $table->text('diagnosis_summary')->nullable();
            $table->text('notes')->nullable();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->foreignId('updated_by')
                ->nullable()
                ->constrained('users')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dental_treatment_plans');
    }
};
