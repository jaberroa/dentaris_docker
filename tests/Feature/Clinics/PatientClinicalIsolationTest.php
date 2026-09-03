<?php

namespace Tests\Feature\Clinics;

use App\Http\Controllers\Api\PatientApiController;
use App\Http\Controllers\PatientController;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class PatientClinicalIsolationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Route::middleware(['web', 'auth', 'clinic.context'])
            ->prefix('__tests/clinic-patients/web')
            ->group(function (): void {
                Route::get('/', [PatientController::class, 'index']);
                Route::get('/search', [PatientController::class, 'search']);
                Route::post('/', [PatientController::class, 'store']);
                Route::get('/{patient}', [PatientController::class, 'show']);
                Route::put('/{patient}', [PatientController::class, 'update']);
                Route::delete('/{patient}', [PatientController::class, 'destroy']);
            });

        Route::middleware(['api', 'auth:sanctum', 'clinic.context'])
            ->prefix('__tests/clinic-patients/api')
            ->group(function (): void {
                Route::get('/', [PatientApiController::class, 'index']);
                Route::post('/', [PatientApiController::class, 'store']);
                Route::get('/{patient}', [PatientApiController::class, 'show']);
                Route::put('/{patient}', [PatientApiController::class, 'update']);
                Route::delete('/{patient}', [PatientApiController::class, 'destroy']);
            });
    }

    public function test_web_listing_and_search_only_expose_the_active_clinic(): void
    {
        $fixture = $this->fixture();
        $local = $this->patient($fixture['user'], $fixture['clinic_id'], 'Paciente Local');
        $foreign = $this->patient($fixture['user'], $fixture['other_clinic_id'], 'Paciente Ajeno');

        $response = $this->actingAs($fixture['user'])
            ->withSession(['clinic_id' => $fixture['clinic_id']])
            ->get('/__tests/clinic-patients/web');

        $response->assertOk()->assertViewHas('patients', function ($patients) use ($local, $foreign): bool {
            $ids = $patients->getCollection()->modelKeys();

            return in_array($local->id, $ids, true) && ! in_array($foreign->id, $ids, true);
        });

        $this->actingAs($fixture['user'])
            ->withSession(['clinic_id' => $fixture['clinic_id']])
            ->getJson('/__tests/clinic-patients/web/search?search=Paciente')
            ->assertOk()
            ->assertJsonPath('total', 1)
            ->assertJsonPath('data.0.id', $local->id);
    }

    public function test_web_direct_access_to_a_foreign_patient_returns_not_found(): void
    {
        $fixture = $this->fixture();
        $foreign = $this->patient($fixture['user'], $fixture['other_clinic_id'], 'Paciente Ajeno');

        $this->actingAs($fixture['user'])
            ->withSession(['clinic_id' => $fixture['clinic_id']])
            ->get("/__tests/clinic-patients/web/{$foreign->id}")
            ->assertNotFound();

        $this->actingAs($fixture['user'])
            ->withSession(['clinic_id' => $fixture['clinic_id']])
            ->delete("/__tests/clinic-patients/web/{$foreign->id}")
            ->assertNotFound();
    }

    public function test_web_creation_assigns_the_server_context_and_rejects_client_ownership(): void
    {
        $fixture = $this->fixture();
        $payload = $this->patientPayload('Creado Web');

        $this->actingAs($fixture['user'])
            ->withSession(['clinic_id' => $fixture['clinic_id']])
            ->post('/__tests/clinic-patients/web', $payload)
            ->assertRedirect();

        $this->assertDatabaseHas('patients', [
            'first_name' => 'Creado Web',
            'clinic_id' => $fixture['clinic_id'],
            'created_by' => $fixture['user']->id,
        ]);

        $this->actingAs($fixture['user'])
            ->withSession(['clinic_id' => $fixture['clinic_id']])
            ->post('/__tests/clinic-patients/web', $payload + ['clinic_id' => $fixture['other_clinic_id']])
            ->assertSessionHasErrors('clinic_id');

        $this->assertDatabaseCount('patients', 1);
    }

    public function test_api_listing_and_direct_binding_are_isolated(): void
    {
        $fixture = $this->fixture();
        $local = $this->patient($fixture['user'], $fixture['clinic_id'], 'Paciente API Local');
        $foreign = $this->patient($fixture['user'], $fixture['other_clinic_id'], 'Paciente API Ajeno');
        $headers = $this->apiHeaders($fixture['user'], $fixture['clinic_id']);

        $this->withHeaders($headers)
            ->getJson('/__tests/clinic-patients/api')
            ->assertOk()
            ->assertJsonPath('total', 1)
            ->assertJsonPath('data.0.id', $local->id);

        $this->withHeaders($headers)
            ->getJson("/__tests/clinic-patients/api/{$foreign->id}")
            ->assertNotFound();
    }

    public function test_api_rejects_a_request_without_clinic_context(): void
    {
        $fixture = $this->fixture();
        $this->patient($fixture['user'], $fixture['clinic_id'], 'Paciente Protegido');

        $this->withHeaders([
            'Authorization' => 'Bearer '.$fixture['user']->createToken('missing-context')->plainTextToken,
            'Accept' => 'application/json',
        ])->getJson('/__tests/clinic-patients/api')
            ->assertForbidden()
            ->assertJsonPath('code', 'CLINIC_CONTEXT_UNAVAILABLE');
    }

    public function test_api_creation_uses_only_the_validated_server_context(): void
    {
        $fixture = $this->fixture();
        $headers = $this->apiHeaders($fixture['user'], $fixture['clinic_id']);
        $payload = $this->patientPayload('Creado API');

        $this->withHeaders($headers)
            ->postJson('/__tests/clinic-patients/api', $payload)
            ->assertCreated()
            ->assertJsonPath('data.first_name', 'Creado API');

        $this->assertDatabaseHas('patients', [
            'first_name' => 'Creado API',
            'clinic_id' => $fixture['clinic_id'],
            'created_by' => $fixture['user']->id,
        ]);

        $this->withHeaders($headers)
            ->postJson('/__tests/clinic-patients/api', $payload + [
                'email' => 'forged@example.test',
                'clinic_id' => $fixture['other_clinic_id'],
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('clinic_id');
    }

    public function test_api_update_preserves_ownership_and_rejects_foreign_records(): void
    {
        $fixture = $this->fixture();
        $local = $this->patient($fixture['user'], $fixture['clinic_id'], 'Paciente Local');
        $foreign = $this->patient($fixture['user'], $fixture['other_clinic_id'], 'Paciente Ajeno');
        $headers = $this->apiHeaders($fixture['user'], $fixture['clinic_id']);
        $payload = $this->patientPayload('Actualizado Local');

        $this->withHeaders($headers)
            ->putJson("/__tests/clinic-patients/api/{$local->id}", $payload)
            ->assertOk()
            ->assertJsonPath('data.first_name', 'Actualizado Local');

        $this->assertDatabaseHas('patients', [
            'id' => $local->id,
            'clinic_id' => $fixture['clinic_id'],
        ]);

        $this->withHeaders($headers)
            ->putJson("/__tests/clinic-patients/api/{$foreign->id}", $payload)
            ->assertNotFound();

        $this->withHeaders($headers)
            ->putJson("/__tests/clinic-patients/api/{$local->id}", $payload + [
                'clinic_id' => $fixture['other_clinic_id'],
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('clinic_id');
    }

    /**
     * @return array{user: User, clinic_id: int, other_clinic_id: int}
     */
    private function fixture(): array
    {
        $user = User::factory()->create(['is_active' => true]);
        $clinicId = $this->clinic('CL-A');
        $otherClinicId = $this->clinic('CL-B');
        $now = now();

        $membershipId = DB::table('clinic_memberships')->insertGetId([
            'clinic_id' => $clinicId,
            'user_id' => $user->id,
            'status' => 'active',
            'activated_at' => $now,
            'suspended_at' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $roleId = DB::table('roles')->insertGetId([
            'name' => 'patient-clinical-test-'.uniqid(),
            'display_name' => 'Acceso clínico de pacientes',
            'permissions' => json_encode(['view_patients', 'manage_patients']),
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('clinic_membership_roles')->insert([
            'clinic_membership_id' => $membershipId,
            'role_id' => $roleId,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return [
            'user' => $user,
            'clinic_id' => $clinicId,
            'other_clinic_id' => $otherClinicId,
        ];
    }

    private function clinic(string $prefix): int
    {
        $now = now();

        return DB::table('clinics')->insertGetId([
            'name' => "Clínica {$prefix}",
            'code' => $prefix.'-'.uniqid(),
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    private function patient(User $user, int $clinicId, string $firstName): Patient
    {
        return Patient::factory()->create([
            'clinic_id' => $clinicId,
            'created_by' => $user->id,
            'first_name' => $firstName,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function patientPayload(string $firstName): array
    {
        return [
            'first_name' => $firstName,
            'last_name' => 'Aislamiento',
            'email' => strtolower(str_replace(' ', '.', $firstName)).'@example.test',
            'birth_date' => '1990-01-01',
            'gender' => 'other',
            'consent_data_processing' => true,
        ];
    }

    /**
     * @return array<string, string>
     */
    private function apiHeaders(User $user, int $clinicId): array
    {
        return [
            'Authorization' => 'Bearer '.$user->createToken('patient-isolation')->plainTextToken,
            'Accept' => 'application/json',
            'X-Clinic-Id' => (string) $clinicId,
        ];
    }
}
