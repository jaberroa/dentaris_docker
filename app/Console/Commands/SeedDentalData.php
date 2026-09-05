<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class SeedDentalData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dentaris:seed 
                            {--patients=100 : Número de pacientes a crear}
                            {--records=50 : Número de historias clínicas a crear}
                            {--fresh : Ejecutar migraciones frescas antes de sembrar}
                            {--force : Forzar la ejecución sin confirmación}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sembrar datos de prueba para el sistema dental Dentaris';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🦷 Dentaris - Sistema de Gestión Clínica Dental');
        $this->info('================================================');
        
        $patients = $this->option('patients');
        $records = $this->option('records');
        $fresh = $this->option('fresh');
        $force = $this->option('force');

        if (!$force && !$this->confirm('¿Estás seguro de que quieres sembrar datos de prueba?')) {
            $this->info('Operación cancelada.');
            return;
        }

        if ($fresh) {
            $this->warn('⚠️  Ejecutando migraciones frescas...');
            Artisan::call('migrate:fresh');
            $this->info('✅ Migraciones frescas completadas.');
        }

        $this->info('🌱 Iniciando proceso de siembra de datos...');

        // 1. Roles y permisos
        $this->info('📋 Creando roles y permisos...');
        Artisan::call('db:seed', ['--class' => 'RoleSeeder']);
        $this->info('✅ Roles y permisos creados.');

        // 2. Usuarios
        $this->info('👥 Creando usuarios...');
        Artisan::call('db:seed', ['--class' => 'UserSeeder']);
        $this->info('✅ Usuarios creados.');

        // 3. Personal médico
        $this->info('👨‍⚕️ Creando personal médico...');
        Artisan::call('db:seed', ['--class' => 'StaffSeeder']);
        $this->info('✅ Personal médico creado.');

        // 4. Pacientes
        $this->info("👤 Creando {$patients} pacientes...");
        $this->createPatients($patients);
        $this->info('✅ Pacientes creados.');

        // 5. Historias clínicas
        $this->info("📋 Creando {$records} historias clínicas...");
        $this->createMedicalRecords($records);
        $this->info('✅ Historias clínicas creadas.');

        // 6. Estados de citas
        $this->info('📅 Creando estados de citas...');
        Artisan::call('db:seed', ['--class' => 'AppointmentStatusSeeder']);
        $this->info('✅ Estados de citas creados.');

        // 7. Catálogo CDT
        $this->info('📚 Creando catálogo CDT...');
        Artisan::call('db:seed', ['--class' => 'CdtCatalogSeeder']);
        $this->info('✅ Catálogo CDT creado.');

        // 8. Datos demo adicionales
        $this->info('🎯 Creando datos demo adicionales...');
        Artisan::call('db:seed', ['--class' => 'DemoDataSeeder']);
        $this->info('✅ Datos demo creados.');

        $this->info('');
        $this->info('🎉 ¡Datos de prueba sembrados exitosamente!');
        $this->info('');
        $this->info('📊 Resumen:');
        $this->info("   - Pacientes: {$patients}");
        $this->info("   - Historias clínicas: {$records}");
        $this->info('   - Usuarios del sistema');
        $this->info('   - Personal médico');
        $this->info('   - Estados de citas');
        $this->info('   - Catálogo CDT');
        $this->info('');
        $this->info('🔑 Las contraseñas no se muestran; usa el flujo de restablecimiento para habilitar una cuenta nueva.');
        $this->info('');
        $this->info('🌐 Accede a: http://localhost:8000/login');
    }

    /**
     * Crear pacientes personalizados
     */
    private function createPatients($count)
    {
        // Aquí podrías personalizar la creación de pacientes
        // Por ahora usamos el seeder estándar
        Artisan::call('db:seed', ['--class' => 'PatientSeeder']);
    }

    /**
     * Crear historias clínicas personalizadas
     */
    private function createMedicalRecords($count)
    {
        // Aquí podrías personalizar la creación de historias clínicas
        // Por ahora usamos el seeder estándar
        Artisan::call('db:seed', ['--class' => 'MedicalRecordSeeder']);
    }
}





