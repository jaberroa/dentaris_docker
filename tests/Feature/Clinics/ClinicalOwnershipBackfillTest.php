<?php

namespace Tests\Feature\Clinics;

use App\Models\Patient;
use App\Models\Staff;
use App\Models\User;
use App\Modules\Clinics\Models\Clinic;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ClinicalOwnershipBackfillTest extends TestCase
{
    use RefreshDatabase;

    public function test_dry_run_does_not_change_pending_rows(): void
    {
        [$clinic, $user] = $this->fixture();
        $this->createPatient($user->id, 'BF-DRY');
        $this->createStaff($user->id, 'BF-STAFF-DRY');

        Artisan::call('clinics:backfill-ownership');

        $this->assertSame(0, DB::table('patients')->where('clinic_id', $clinic->id)->count());
        $this->assertSame(0, DB::table('staff')->where('clinic_id', $clinic->id)->count());
        $this->assertStringContainsString('"status":"dry_run"', Artisan::output());
    }

    public function test_execute_is_atomic_and_idempotent(): void
    {
        [$clinic, $user] = $this->fixture();
        $patient = $this->createPatient($user->id, 'BF-EXEC');
        $staff = $this->createStaff($user->id, 'BF-STAFF-EXEC');
        $patientUpdatedAt = $patient->updated_at;
        $staffUpdatedAt = $staff->updated_at;

        $this->assertSame(0, Artisan::call('clinics:backfill-ownership', ['--execute' => true]));
        $this->assertSame(1, DB::table('patients')->where('clinic_id', $clinic->id)->count());
        $this->assertSame(1, DB::table('staff')->where('clinic_id', $clinic->id)->count());
        $this->assertSame(0, DB::table('patients')->whereNull('clinic_id')->count());
        $this->assertSame(0, DB::table('staff')->whereNull('clinic_id')->count());
        $this->assertEquals($patientUpdatedAt, Patient::query()->findOrFail($patient->id)->updated_at);
        $this->assertEquals($staffUpdatedAt, Staff::query()->findOrFail($staff->id)->updated_at);

        Artisan::call('clinics:backfill-ownership', ['--execute' => true]);
        $this->assertStringContainsString('"patients_updated":0', Artisan::output());
        $this->assertStringContainsString('"staff_updated":0', Artisan::output());
    }

    private function fixture(): array
    {
        $clinic = Clinic::query()->create(['code' => 'DEN-CL-001', 'name' => 'Dentaris', 'is_active' => true]);
        $user = User::factory()->create(['is_active' => true]);
        $now = now();
        DB::table('clinic_memberships')->insert([
            'user_id' => $user->id,
            'clinic_id' => $clinic->id,
            'status' => 'active',
            'activated_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return [$clinic, $user];
    }

    private function createPatient(int $userId, string $code): Patient
    {
        return Patient::query()->create([
            'patient_code' => $code,
            'first_name' => 'Backfill',
            'last_name' => 'Patient',
            'birth_date' => '1990-01-01',
            'gender' => 'other',
            'created_by' => $userId,
        ]);
    }

    private function createStaff(int $userId, string $employeeId): Staff
    {
        return Staff::query()->create([
            'user_id' => $userId,
            'employee_id' => $employeeId,
        ]);
    }
}
