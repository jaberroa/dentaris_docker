<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            UserSeeder::class,
            StaffSeeder::class,
            PatientSeeder::class,
            MedicalRecordSeeder::class,
            AppointmentStatusSeeder::class,
            AppointmentSeeder::class,
            CdtCatalogSeeder::class,
            TreatmentPlanSeeder::class,
            PaymentSeeder::class,
            DemoDataSeeder::class,
        ]);
    }
}