<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class TestApiEndpoints extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'api:test {--email= : Correo del usuario que autoriza la prueba}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Probar endpoints de la API';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Probando endpoints de la API...');

        // Obtener token de autenticación
        $token = $this->getAuthToken();
        if (! $token) {
            $this->error('No se pudo obtener token de autenticación');

            return;
        }

        $baseUrl = config('app.url').'/api';
        $headers = [
            'Authorization' => 'Bearer '.$token,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];

        // Probar endpoints
        $this->testEndpoint($baseUrl.'/dashboard/kpis', $headers, 'Dashboard KPIs');
        $this->testEndpoint($baseUrl.'/patients', $headers, 'Lista de Pacientes');
        $this->testEndpoint($baseUrl.'/appointments', $headers, 'Lista de Citas');
        $this->testEndpoint($baseUrl.'/inventory', $headers, 'Lista de Inventario');
        $this->testEndpoint($baseUrl.'/reports/kpis', $headers, 'Reportes KPIs');
        $this->testEndpoint($baseUrl.'/config/appointments/statuses', $headers, 'Configuración Estados');

        $this->info('Pruebas de API completadas.');
    }

    protected function getAuthToken()
    {
        try {
            $email = trim((string) $this->option('email'));
            $user = $email !== '' ? User::where('email', $email)->first() : User::first();
            if (! $user) {
                $this->error('No hay usuarios en la base de datos');

                return null;
            }

            $password = $this->secret('Contraseña del usuario (no se mostrará ni registrará)');
            if (! is_string($password) || $password === '') {
                $this->error('La contraseña es obligatoria para probar el login de la API');

                return null;
            }

            $response = Http::post(config('app.url').'/api/login', [
                'email' => $user->email,
                'password' => $password,
            ]);

            if ($response->successful()) {
                $data = $response->json();

                return $data['token'] ?? null;
            }

            $this->error('Error en login: '.$response->body());

            return null;
        } catch (\Exception $e) {
            $this->error('Error obteniendo token: '.$e->getMessage());

            return null;
        }
    }

    protected function testEndpoint($url, $headers, $description)
    {
        try {
            $response = Http::withHeaders($headers)->get($url);

            if ($response->successful()) {
                $data = $response->json();
                $this->line("✅ {$description}: OK");
                if (isset($data['success']) && $data['success']) {
                    $this->line("   Mensaje: {$data['message']}");
                }
            } else {
                $this->line("❌ {$description}: Error {$response->status()}");
                $this->line('   Respuesta: '.$response->body());
            }
        } catch (\Exception $e) {
            $this->line("❌ {$description}: Exception - ".$e->getMessage());
        }
    }
}
