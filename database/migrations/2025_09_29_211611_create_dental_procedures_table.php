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
        Schema::create('dental_procedures', function (Blueprint $table) {
            $table->id();

            $table->foreignId('dental_treatment_plan_id')
                ->constrained('dental_treatment_plans')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreignId('dental_odontogram_id')
                ->nullable()
                ->constrained('dental_odontograms')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->foreignId('dental_periodontogram_id')
                ->nullable()
                ->constrained('dental_periodontograms')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->string('procedure_name');
            $table->string('procedure_code', 30)->nullable();
            $table->enum('procedure_type', ['odontogram', 'periodontogram']);

            $table->string('tooth_number', 3)->nullable();
            $table->string('surface', 20)->nullable();
            $table->string('periodontal_zone', 50)->nullable();

            $table->enum('status', ['pending', 'in_progress', 'completed', 'cancelled'])->default('pending');
            $table->unsignedSmallInteger('priority')->default(2);

            $table->unsignedSmallInteger('estimated_sessions')->default(1);
            $table->unsignedSmallInteger('completed_sessions')->default(0);

            $table->unsignedInteger('estimated_time_minutes')->default(30);
            $table->unsignedInteger('actual_time_minutes')->nullable();

            $table->decimal('estimated_cost', 12, 2)->default(0);
            $table->decimal('actual_cost', 12, 2)->default(0);

            $table->date('scheduled_date')->nullable();
            $table->date('started_date')->nullable();
            $table->date('completed_date')->nullable();

            $table->foreignId('responsible_staff_id')
                ->nullable()
                ->constrained('staff')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dental_procedures');
    }
};
