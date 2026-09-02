<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Introduce una pertenencia clínica aditiva para las entidades raíz.
     *
     * Las columnas permanecen nullable durante la transición: el backfill
     * histórico y el endurecimiento a NOT NULL requieren mandatos separados.
     */
    public function up(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->foreignId('clinic_id')
                ->nullable()
                ->after('id')
                ->constrained()
                ->restrictOnDelete();
            $table->index(['clinic_id', 'is_active'], 'patients_clinic_active_index');
        });

        Schema::table('staff', function (Blueprint $table) {
            $table->foreignId('clinic_id')
                ->nullable()
                ->after('id')
                ->constrained()
                ->restrictOnDelete();
            $table->index(['clinic_id', 'is_active'], 'staff_clinic_active_index');
            $table->unique(['clinic_id', 'user_id'], 'staff_clinic_user_unique');
        });
    }

    public function down(): void
    {
        Schema::table('staff', function (Blueprint $table) {
            $table->dropUnique('staff_clinic_user_unique');
            $table->dropIndex('staff_clinic_active_index');
            $table->dropForeign(['clinic_id']);
            $table->dropColumn('clinic_id');
        });

        Schema::table('patients', function (Blueprint $table) {
            $table->dropIndex('patients_clinic_active_index');
            $table->dropForeign(['clinic_id']);
            $table->dropColumn('clinic_id');
        });
    }
};
