<?php

namespace Tests\Feature\Clinics;

use App\Http\Middleware\CheckPermission;
use App\Http\Requests\Clinics\AppointmentRequest;
use App\Http\Requests\Clinics\MedicalRecordRequest;
use App\Models\Role;
use App\Models\User;
use App\Modules\Clinics\Data\ClinicContext;
use App\Modules\Clinics\Services\ClinicalRelatedRecordAccessService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class ClinicalPermissionMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_clinic_membership_role_grants_the_requested_permission(): void
    {
        $fixture = $this->fixture(['view_inventory']);
        $request = $this->requestFor($fixture['user'], $fixture['context']);

        $response = $this->middleware()->handle(
            $request,
            fn (): Response => response('allowed', 200),
            'view_inventory',
        );

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('allowed', $response->getContent());
        $this->assertFalse($fixture['user']->roles()->exists());
    }

    public function test_global_role_cannot_grant_a_clinical_permission(): void
    {
        $fixture = $this->fixture([]);
        $globalRole = Role::create([
            'name' => 'global-'.uniqid(),
            'display_name' => 'Rol global sin alcance clínico',
            'permissions' => ['manage_billing'],
            'is_active' => true,
        ]);
        $fixture['user']->roles()->attach($globalRole);
        $request = $this->requestFor($fixture['user'], $fixture['context'], true);

        $response = $this->middleware()->handle(
            $request,
            fn (): Response => response('must-not-run', 200),
            'manage_billing',
        );

        $this->assertSame(403, $response->getStatusCode());
        $this->assertSame([
            'error' => 'Unauthorized',
            'message' => 'No tienes permisos para realizar esta acción',
        ], $response->getData(true));
    }

    public function test_permission_fails_closed_without_a_validated_clinic_context(): void
    {
        $fixture = $this->fixture(['view_inventory']);
        $request = $this->requestFor($fixture['user'], expectsJson: true);

        $response = $this->middleware()->handle(
            $request,
            fn (): Response => response('must-not-run', 200),
            'view_inventory',
        );

        $this->assertSame(403, $response->getStatusCode());
    }

    public function test_context_for_another_user_is_rejected(): void
    {
        $fixture = $this->fixture(['view_inventory']);
        $otherUser = User::factory()->create(['is_active' => true]);
        $request = $this->requestFor($otherUser, $fixture['context'], true);

        $response = $this->middleware()->handle(
            $request,
            fn (): Response => response('must-not-run', 200),
            'view_inventory',
        );

        $this->assertSame(403, $response->getStatusCode());
    }

    public function test_legacy_context_attribute_is_resolved_consistently(): void
    {
        $fixture = $this->fixture(['view_inventory']);
        $request = Request::create('/protected', 'GET');
        $request->setUserResolver(fn (): User => $fixture['user']);
        $request->attributes->set('clinic.context', $fixture['context']);

        $response = $this->middleware()->handle(
            $request,
            fn (): Response => response('allowed', 200),
            'view_inventory',
        );

        $this->assertSame(200, $response->getStatusCode());
    }

    public function test_suspended_membership_revokes_a_previously_resolved_permission(): void
    {
        $fixture = $this->fixture(['manage_inventory']);
        DB::table('clinic_memberships')
            ->where('id', $fixture['context']->membershipId)
            ->update(['suspended_at' => now()]);
        $request = $this->requestFor($fixture['user'], $fixture['context'], true);

        $response = $this->middleware()->handle(
            $request,
            fn (): Response => response('must-not-run', 200),
            'manage_inventory',
        );

        $this->assertSame(403, $response->getStatusCode());
    }

    public function test_inactive_clinic_role_revokes_permission(): void
    {
        $fixture = $this->fixture(['export_inventory']);
        DB::table('roles')->where('id', $fixture['role_id'])->update(['is_active' => false]);
        $request = $this->requestFor($fixture['user'], $fixture['context'], true);

        $response = $this->middleware()->handle(
            $request,
            fn (): Response => response('must-not-run', 200),
            'export_inventory',
        );

        $this->assertSame(403, $response->getStatusCode());
    }

    public function test_unauthenticated_web_request_keeps_the_login_redirect_contract(): void
    {
        $request = Request::create('/protected', 'GET');

        $response = $this->middleware()->handle(
            $request,
            fn (): Response => response('must-not-run', 200),
            'view_inventory',
        );

        $this->assertTrue($response->isRedirect(route('login')));
    }

    public function test_appointment_request_rejects_cross_clinic_relations_and_client_ownership(): void
    {
        $fixture = $this->fixture([]);
        $otherClinicId = $this->clinicId('REQUEST-B');
        $localPatientId = $this->patientId($fixture['user'], $fixture['context']->clinicId, 'LOCAL');
        $localStaffId = $this->staffId($fixture['context']->clinicId, 'LOCAL');
        $foreignStaffId = $this->staffId($otherClinicId, 'FOREIGN');
        $statusId = $this->appointmentStatusId();

        $valid = $this->appointmentRequest([
            'patient_id' => $localPatientId,
            'staff_id' => $localStaffId,
            'appointment_status_id' => $statusId,
            'appointment_date' => now()->addDay()->toDateString(),
            'start_time' => '09:00',
            'duration' => 30,
            'type' => 'consulta',
        ], $fixture['user'], $fixture['context']);

        $this->assertTrue($valid->authorize());
        $this->assertTrue(Validator::make($valid->all(), $valid->rules())->passes());

        $forged = $this->appointmentRequest([
            ...$valid->all(),
            'staff_id' => $foreignStaffId,
            'clinic_id' => $otherClinicId,
        ], $fixture['user'], $fixture['context']);
        $validator = Validator::make($forged->all(), $forged->rules());

        $this->assertFalse($validator->passes());
        $this->assertTrue($validator->errors()->has('staff_id'));
        $this->assertTrue($validator->errors()->has('clinic_id'));
    }

    public function test_medical_record_request_requires_a_matching_scoped_appointment(): void
    {
        $fixture = $this->fixture([]);
        $clinicId = $fixture['context']->clinicId;
        $patientId = $this->patientId($fixture['user'], $clinicId, 'RECORD-A');
        $otherPatientId = $this->patientId($fixture['user'], $clinicId, 'RECORD-B');
        $staffId = $this->staffId($clinicId, 'RECORD');
        $appointmentId = $this->appointmentId(
            $patientId,
            $staffId,
            $fixture['user'],
            $this->appointmentStatusId(),
            'RECORD',
        );

        $valid = $this->medicalRecordRequest([
            'patient_id' => $patientId,
            'staff_id' => $staffId,
            'record_type' => 'consulta',
            'chief_complaint' => 'Control clínico',
            'appointment_id' => $appointmentId,
        ], $fixture['user'], $fixture['context']);
        $validator = Validator::make($valid->all(), $valid->rules());
        $validator->after($valid->after());

        $this->assertTrue($valid->authorize());
        $this->assertTrue($validator->passes());

        $mismatched = $this->medicalRecordRequest([
            ...$valid->all(),
            'patient_id' => $otherPatientId,
        ], $fixture['user'], $fixture['context']);
        $validator = Validator::make($mismatched->all(), $mismatched->rules());
        $validator->after($mismatched->after());

        $this->assertFalse($validator->passes());
        $this->assertTrue($validator->errors()->has('appointment_id'));
    }

    public function test_related_record_queries_exclude_cross_clinic_links(): void
    {
        $fixture = $this->fixture([]);
        $clinicId = $fixture['context']->clinicId;
        $otherClinicId = $this->clinicId('RELATED-B');
        $localPatientId = $this->patientId($fixture['user'], $clinicId, 'RELATED-A');
        $foreignPatientId = $this->patientId($fixture['user'], $otherClinicId, 'RELATED-B');
        $localStaffId = $this->staffId($clinicId, 'RELATED-A');
        $foreignStaffId = $this->staffId($otherClinicId, 'RELATED-B');
        $statusId = $this->appointmentStatusId();
        $localAppointmentId = $this->appointmentId(
            $localPatientId,
            $localStaffId,
            $fixture['user'],
            $statusId,
            'LOCAL',
        );
        $crossAppointmentId = $this->appointmentId(
            $localPatientId,
            $foreignStaffId,
            $fixture['user'],
            $statusId,
            'CROSS',
        );
        $foreignAppointmentId = $this->appointmentId(
            $foreignPatientId,
            $foreignStaffId,
            $fixture['user'],
            $statusId,
            'FOREIGN',
        );

        $localRecordId = $this->medicalRecordId(
            $localPatientId,
            $localStaffId,
            $fixture['user'],
            $localAppointmentId,
            'LOCAL',
        );
        $this->medicalRecordId(
            $localPatientId,
            $localStaffId,
            $fixture['user'],
            $crossAppointmentId,
            'CROSS',
        );
        $this->medicalRecordId(
            $foreignPatientId,
            $foreignStaffId,
            $fixture['user'],
            $foreignAppointmentId,
            'FOREIGN',
        );

        $service = app(ClinicalRelatedRecordAccessService::class);

        $this->assertSame(
            [$localAppointmentId],
            $service->appointments($fixture['context'])->orderBy('id')->pluck('id')->all(),
        );
        $this->assertSame(
            [$localRecordId],
            $service->medicalRecords($fixture['context'])->orderBy('id')->pluck('id')->all(),
        );
    }

    /**
     * @param  list<string>  $permissions
     * @return array{user: User, context: ClinicContext, role_id: int}
     */
    private function fixture(array $permissions): array
    {
        $now = now();
        $user = User::factory()->create(['is_active' => true]);
        $clinicId = DB::table('clinics')->insertGetId([
            'name' => 'Clínica Middleware '.uniqid(),
            'code' => 'MW-'.uniqid(),
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
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
            'name' => 'membership-'.uniqid(),
            'display_name' => 'Rol de membresía clínica',
            'permissions' => json_encode($permissions, JSON_THROW_ON_ERROR),
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
            'context' => new ClinicContext($user->id, $clinicId, $membershipId),
            'role_id' => $roleId,
        ];
    }

    private function requestFor(
        User $user,
        ?ClinicContext $context = null,
        bool $expectsJson = false,
    ): Request {
        $request = Request::create('/protected', 'GET');
        $request->setUserResolver(fn (): User => $user);

        if ($context !== null) {
            $request->attributes->set(ClinicContext::class, $context);
            $request->attributes->set('clinic.context', $context);
        }

        if ($expectsJson) {
            $request->headers->set('Accept', 'application/json');
        }

        return $request;
    }

    private function middleware(): CheckPermission
    {
        return app(CheckPermission::class);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function appointmentRequest(array $payload, User $user, ClinicContext $context): AppointmentRequest
    {
        $request = AppointmentRequest::create('/appointments', 'POST', $payload);
        $request->setUserResolver(fn (): User => $user);
        $request->attributes->set('clinic.context', $context);

        return $request;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function medicalRecordRequest(array $payload, User $user, ClinicContext $context): MedicalRecordRequest
    {
        $request = MedicalRecordRequest::create('/medical-records', 'POST', $payload);
        $request->setUserResolver(fn (): User => $user);
        $request->attributes->set('clinic.context', $context);

        return $request;
    }

    private function clinicId(string $suffix): int
    {
        $now = now();

        return DB::table('clinics')->insertGetId([
            'name' => 'Clínica '.$suffix,
            'code' => $suffix.'-'.uniqid(),
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    private function patientId(User $creator, int $clinicId, string $suffix): int
    {
        $now = now();

        return DB::table('patients')->insertGetId([
            'clinic_id' => $clinicId,
            'patient_code' => 'PX-'.$suffix.'-'.uniqid(),
            'first_name' => 'Paciente',
            'last_name' => $suffix,
            'birth_date' => '1990-01-01',
            'gender' => 'other',
            'is_active' => true,
            'created_by' => $creator->id,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    private function staffId(int $clinicId, string $suffix): int
    {
        $now = now();
        $staffUser = User::factory()->create(['is_active' => true]);

        return DB::table('staff')->insertGetId([
            'clinic_id' => $clinicId,
            'user_id' => $staffUser->id,
            'employee_id' => 'EMP-'.$suffix.'-'.uniqid(),
            'is_available' => true,
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    private function appointmentStatusId(): int
    {
        $now = now();

        return DB::table('appointment_statuses')->insertGetId([
            'name' => 'scheduled-'.uniqid(),
            'display_name' => 'Programada',
            'is_active' => true,
            'is_final' => false,
            'sort_order' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    private function appointmentId(
        int $patientId,
        int $staffId,
        User $creator,
        int $statusId,
        string $suffix,
    ): int {
        $now = now();

        return DB::table('appointments')->insertGetId([
            'appointment_code' => 'M13A-'.$suffix.'-'.uniqid(),
            'patient_id' => $patientId,
            'staff_id' => $staffId,
            'appointment_status_id' => $statusId,
            'appointment_date' => $now->copy()->addDay()->toDateString(),
            'start_time' => '09:00:00',
            'end_time' => '09:30:00',
            'duration' => 30,
            'type' => 'consulta',
            'created_by' => $creator->id,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    private function medicalRecordId(
        int $patientId,
        int $staffId,
        User $creator,
        ?int $appointmentId,
        string $suffix,
    ): int {
        $now = now();

        return DB::table('medical_records')->insertGetId([
            'patient_id' => $patientId,
            'appointment_id' => $appointmentId,
            'staff_id' => $staffId,
            'record_type' => 'consulta',
            'chief_complaint' => 'Control '.$suffix,
            'present_illness' => '',
            'medical_history' => '',
            'dental_history' => '',
            'family_history' => '',
            'social_history' => '',
            'clinical_examination' => '',
            'oral_examination' => '',
            'diagnostic_impression' => '',
            'treatment_plan' => '',
            'recommendations' => '',
            'is_confidential' => false,
            'created_by' => $creator->id,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }
}
