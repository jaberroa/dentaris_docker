<?php

namespace Tests\Feature\Clinics;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\InteractsWithClinicalContext;
use Tests\TestCase;

class ClinicOwnedDomainQaFixtureTest extends TestCase
{
    use InteractsWithClinicalContext;
    use RefreshDatabase;

    public function test_command_creates_five_visible_labelled_records_per_owned_view_and_is_idempotent(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $context = $this->clinicalContextFor($user, [
            'view_inventory', 'manage_inventory', 'view_billing', 'manage_billing',
            'view_payments', 'manage_payments',
        ]);
        DB::table('clinics')->where('id', $context->clinicId)->update(['code' => 'DEN-CL-001']);

        $first = Artisan::call('clinics:create-owned-domain-qa', [
            '--actor-email' => $user->email,
            '--count' => 5,
            '--execute' => true,
        ]);
        $firstOutput = Artisan::output();

        $this->assertSame(0, $first, $firstOutput);
        $this->assertStringContainsString('"inventory":5', $firstOutput, $firstOutput);
        $this->assertStringContainsString('"billing":5', $firstOutput, $firstOutput);
        $this->assertStringContainsString('"payments":5', $firstOutput, $firstOutput);
        $this->assertSame(5, DB::table('inventory')->where('clinic_id', $context->clinicId)->count());
        $this->assertSame(5, DB::table('inventory_movements')->where('clinic_id', $context->clinicId)->count());
        $this->assertSame(5, DB::table('invoices')->where('clinic_id', $context->clinicId)->count());
        $this->assertSame(5, DB::table('invoice_items')->count());
        $this->assertSame(5, DB::table('payments')->where('clinic_id', $context->clinicId)->count());
        $this->assertSame(5, DB::table('products')->where('name', 'like', 'QA/PRUEBA 14A%')->count());
        $this->assertSame(5, DB::table('inventory_locations')->where('name', 'like', 'QA/PRUEBA 14A%')->count());
        $this->assertSame(1, DB::table('patients')->where('patient_code', "QA14A-PAT-C{$context->clinicId}")->count());
        $this->assertSame(1, DB::table('staff')->where('employee_id', "QA14A-STAFF-C{$context->clinicId}")->count());
        $this->assertSame(0, DB::table('suppliers')->count());
        $this->assertSame(0, DB::table('purchases')->count());

        $second = app(\App\Modules\Clinics\Services\ClinicOwnedDomainQaFixtureService::class)
            ->execute('DEN-CL-001', $user->email, 5);

        $this->assertSame(0, $second['created_total']);
        $this->assertSame(5, DB::table('inventory')->count());
        $this->assertSame(5, DB::table('invoices')->count());
        $this->assertSame(5, DB::table('payments')->count());
        $this->assertSame(2, DB::table('activity_log')->where('description', 'clinic_owned_domains.qa_created')->count());
    }

    public function test_dry_run_rejects_less_than_five_records(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $context = $this->clinicalContextFor($user, [
            'view_inventory', 'manage_inventory', 'view_billing', 'manage_billing',
            'view_payments', 'manage_payments',
        ]);
        DB::table('clinics')->where('id', $context->clinicId)->update(['code' => 'DEN-CL-001']);

        $exit = Artisan::call('clinics:create-owned-domain-qa', [
            '--actor-email' => $user->email,
            '--count' => 4,
        ]);

        $this->assertSame(1, $exit);
        $this->assertStringContainsString('between 5 and 25', Artisan::output());
        $this->assertSame(0, DB::table('inventory')->count());
        $this->assertSame(0, DB::table('invoices')->count());
        $this->assertSame(0, DB::table('payments')->count());
    }

    public function test_execute_fails_closed_when_actor_cannot_view_or_manage_payments(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $context = $this->clinicalContextFor($user, [
            'view_inventory', 'manage_inventory', 'view_billing', 'manage_billing',
        ]);
        DB::table('clinics')->where('id', $context->clinicId)->update(['code' => 'DEN-CL-001']);

        $exit = Artisan::call('clinics:create-owned-domain-qa', [
            '--actor-email' => $user->email,
            '--count' => 5,
            '--execute' => true,
        ]);

        $this->assertSame(1, $exit);
        $this->assertStringContainsString('view_payments,manage_payments', Artisan::output());
        $this->assertSame(0, DB::table('inventory')->count());
        $this->assertSame(0, DB::table('invoices')->count());
        $this->assertSame(0, DB::table('payments')->count());
    }
}
