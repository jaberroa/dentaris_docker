<?php

namespace Tests\Feature\Clinics;

use App\Exports\PatientsExport;
use App\Exports\StaffExport;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\MedicalRecordController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\QuoteController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\SupplierController;
use App\Models\Patient;
use App\Models\Staff;
use App\Models\User;
use App\Modules\Clinics\Data\ClinicContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Routing\Route as RoutingRoute;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ViewErrorBag;
use LogicException;
use Tests\TestCase;

class ClinicalRouteContractTest extends TestCase
{
    use RefreshDatabase;

    public function test_patient_and_staff_web_routes_require_clinic_context_and_the_expected_permission(): void
    {
        $routes = [
            'patients.index' => 'can:viewAny,App\Models\Patient',
            'patients.search' => 'can:viewAny,App\Models\Patient',
            'patients.show' => 'can:view,patient',
            'patients.export.excel' => 'can:exportAny,App\Models\Patient',
            'patients.export.pdf' => 'can:exportAny,App\Models\Patient',
            'patients.export.history' => 'can:export,patient',
            'patients.create' => 'can:create,App\Models\Patient',
            'patients.store' => 'can:create,App\Models\Patient',
            'patients.edit' => 'can:update,patient',
            'patients.update' => 'can:update,patient',
            'patients.destroy' => 'can:delete,patient',
            'patients.update.gender' => 'can:update,patient',
            'patients.update.status' => 'can:update,patient',
            'staff.index' => 'can:viewAny,App\Models\Staff',
            'staff.show' => 'can:view,staff',
            'staff.export.excel' => 'can:exportAny,App\Models\Staff',
            'staff.export.pdf' => 'can:exportAny,App\Models\Staff',
            'staff.create' => 'can:create,App\Models\Staff',
            'staff.store' => 'can:create,App\Models\Staff',
            'staff.edit' => 'can:update,staff',
            'staff.update' => 'can:update,staff',
            'staff.destroy' => 'can:delete,staff',
        ];

        foreach ($routes as $name => $permission) {
            $route = Route::getRoutes()->getByName($name);

            $this->assertNotNull($route, "Missing route [{$name}].");
            $this->assertContains('clinic.context', $route->gatherMiddleware(), $name);
            $this->assertContains($permission, $route->gatherMiddleware(), $name);
        }
    }

    public function test_appointments_medical_records_inventory_and_billing_run_clinic_context_before_permissions(): void
    {
        $routes = [
            'appointments.index' => 'permission:view_appointments',
            'appointments.search.staff' => 'permission:view_appointments',
            'appointments.store' => 'permission:manage_appointments',
            'appointments.update' => 'permission:manage_appointments',
            'medical-records.index' => 'permission:view_medical_records',
            'medical-records.store' => 'permission:manage_medical_records',
            'medical-records.update' => 'permission:manage_medical_records',
            'medical-records.export' => 'permission:view_medical_records',
            'inventory.movements' => 'permission:view_inventory',
            'inventory.locations.index' => 'permission:view_inventory',
            'inventory.locations.store' => 'permission:manage_inventory',
            'inventory.export' => 'permission:export_inventory',
            'billing.create' => 'permission:manage_billing',
            'billing.store' => 'permission:manage_billing',
            'billing.update' => 'permission:manage_billing',
        ];

        foreach ($routes as $name => $permission) {
            $route = Route::getRoutes()->getByName($name);
            $middleware = $route?->gatherMiddleware() ?? [];
            $contextIndex = array_search('clinic.context', $middleware, true);
            $permissionIndex = array_search($permission, $middleware, true);

            $this->assertNotNull($route, "Missing route [{$name}].");
            $this->assertNotFalse($contextIndex, "Missing clinic.context on [{$name}].");
            $this->assertNotFalse($permissionIndex, "Missing {$permission} on [{$name}].");
            $this->assertLessThan($permissionIndex, $contextIndex, "clinic.context must run before {$permission} on [{$name}].");
        }
    }

    public function test_static_appointment_medical_record_and_billing_routes_are_not_shadowed(): void
    {
        $expected = [
            '/appointments/search-staff' => AppointmentController::class.'@searchStaff',
            '/appointments/create' => AppointmentController::class.'@create',
            '/medical-records/create' => MedicalRecordController::class.'@create',
            '/billing/create' => BillingController::class.'@create',
        ];

        foreach ($expected as $uri => $action) {
            $route = Route::getRoutes()->match(Request::create($uri, 'GET'));

            $this->assertSame($action, $route->getActionName(), $uri);
        }
    }

    public function test_specific_create_routes_are_not_shadowed_by_model_routes(): void
    {
        $expected = [
            '/patients/create' => PatientController::class.'@create',
            '/staff/create' => StaffController::class.'@create',
            '/suppliers/create' => SupplierController::class.'@create',
            '/purchases/create' => PurchaseController::class.'@create',
            '/quotes/create' => QuoteController::class.'@create',
        ];

        foreach ($expected as $uri => $action) {
            $route = Route::getRoutes()->match(Request::create($uri, 'GET'));
            $this->assertSame($action, $route->getActionName(), $uri);
        }
    }

    public function test_patient_api_routes_require_active_user_clinic_context_and_permissions(): void
    {
        $expected = [
            'GET api/patients' => 'can:viewAny,App\Models\Patient',
            'GET api/patients/{patient}' => 'can:view,patient',
            'POST api/patients' => 'can:create,App\Models\Patient',
            'PUT api/patients/{patient}' => 'can:update,patient',
            'DELETE api/patients/{patient}' => 'can:delete,patient',
        ];

        foreach ($expected as $contract => $permission) {
            [$method, $uri] = explode(' ', $contract, 2);
            $route = collect(Route::getRoutes()->getRoutes())->first(
                fn (RoutingRoute $candidate): bool => $candidate->uri() === $uri
                    && in_array($method, $candidate->methods(), true)
            );

            $this->assertNotNull($route, "Missing route [{$contract}].");
            $this->assertContains('auth:sanctum', $route->gatherMiddleware(), $contract);
            $this->assertContains('active', $route->gatherMiddleware(), $contract);
            $this->assertContains('clinic.context', $route->gatherMiddleware(), $contract);
            $this->assertContains($permission, $route->gatherMiddleware(), $contract);
        }
    }

    public function test_selector_is_reachable_without_context_and_owned_domains_fail_closed_before_permissions(): void
    {
        foreach (['clinics.select', 'clinics.context.store', 'clinics.context.destroy'] as $name) {
            $route = Route::getRoutes()->getByName($name);

            $this->assertNotNull($route, "Missing route [{$name}].");
            $this->assertContains('auth', $route->gatherMiddleware(), $name);
            $this->assertContains('clinic.selection', $route->gatherMiddleware(), $name);
            $this->assertNotContains('clinic.context', $route->gatherMiddleware(), $name);
        }

        $domains = [
            'inventory.index' => 'clinic.domain.ready:inventory',
            'inventory.show' => 'clinic.domain.ready:inventory',
            'inventory.export' => 'clinic.domain.ready:inventory',
            'billing.index' => 'clinic.domain.ready:billing',
            'billing.show' => 'clinic.domain.ready:billing',
            'payments.index' => 'clinic.domain.ready:billing',
            'payments.store' => 'clinic.domain.ready:billing',
            'suppliers.index' => 'clinic.domain.ready:procurement',
            'suppliers.store' => 'clinic.domain.ready:procurement',
            'purchases.index' => 'clinic.domain.ready:procurement',
            'purchases.store' => 'clinic.domain.ready:procurement',
            'quotes.index' => 'clinic.domain.ready:quotes',
            'quotes.store' => 'clinic.domain.ready:quotes',
        ];

        foreach ($domains as $name => $readiness) {
            $route = Route::getRoutes()->getByName($name);
            $middleware = $route?->gatherMiddleware() ?? [];

            $this->assertNotNull($route, "Missing route [{$name}].");
            $this->assertContains('clinic.context', $middleware, $name);
            $this->assertContains($readiness, $middleware, $name);
            $this->assertLessThan(
                array_search($readiness, $middleware, true),
                array_search('clinic.context', $middleware, true),
                "clinic.context must run before {$readiness} on [{$name}].",
            );
        }
    }

    public function test_web_and_api_patient_lists_reject_an_absent_clinic_context(): void
    {
        $user = User::factory()->create(['is_active' => true]);

        $this->actingAs($user)->get('/patients')->assertForbidden();
        $this->actingAs($user, 'sanctum')->getJson('/api/patients')
            ->assertForbidden()
            ->assertJsonPath('code', 'CLINIC_CONTEXT_UNAVAILABLE');
    }

    public function test_patient_export_is_limited_to_the_validated_clinic(): void
    {
        [$context, $clinicId, $otherClinicId] = $this->clinicContextFixture();
        $user = User::factory()->create();
        $local = Patient::factory()->create(['created_by' => $user->id]);
        $foreign = Patient::factory()->create(['created_by' => $user->id]);
        $local->forceFill(['clinic_id' => $clinicId])->save();
        $foreign->forceFill(['clinic_id' => $otherClinicId])->save();

        $ids = (new PatientsExport([], $context))->collection()->pluck('id');

        $this->assertSame([$local->id], $ids->all());
        $this->assertFalse($ids->contains($foreign->id));
    }

    public function test_staff_export_keeps_search_or_conditions_inside_the_clinic_scope(): void
    {
        [$context, $clinicId, $otherClinicId] = $this->clinicContextFixture();
        $localUser = User::factory()->create(['name' => 'Local Professional']);
        $foreignUser = User::factory()->create(['name' => 'Foreign Professional']);
        $local = $this->createStaff($localUser, $clinicId, 'General');
        $foreign = $this->createStaff($foreignUser, $otherClinicId, 'NeedleSearch');

        $ids = (new StaffExport(['search' => 'NeedleSearch'], $context))->collection()->pluck('id');

        $this->assertSame([], $ids->all());
        $this->assertFalse($ids->contains($foreign->id));
        $this->assertDatabaseHas('staff', ['id' => $local->id, 'clinic_id' => $clinicId]);
    }

    public function test_exports_fail_closed_without_a_validated_clinic_context(): void
    {
        $this->expectException(LogicException::class);

        new PatientsExport();
    }

    public function test_select_components_only_load_records_from_the_active_clinic(): void
    {
        [$context, $clinicId, $otherClinicId] = $this->clinicContextFixture();
        $user = User::factory()->create();
        $localPatient = Patient::factory()->create(['created_by' => $user->id]);
        $foreignPatient = Patient::factory()->create(['created_by' => $user->id]);
        $localPatient->forceFill(['clinic_id' => $clinicId])->save();
        $foreignPatient->forceFill(['clinic_id' => $otherClinicId])->save();
        $localStaff = $this->createStaff(User::factory()->create(), $clinicId, 'General');
        $foreignStaff = $this->createStaff(User::factory()->create(), $otherClinicId, 'General');
        request()->attributes->set(ClinicContext::class, $context);
        view()->share('errors', new ViewErrorBag());

        $patientHtml = Blade::render('<x-patient-select />');
        $staffHtml = Blade::render('<x-staff-select />');

        $this->assertStringContainsString('value="'.$localPatient->id.'"', $patientHtml);
        $this->assertStringNotContainsString('value="'.$foreignPatient->id.'"', $patientHtml);
        $this->assertStringContainsString('value="'.$localStaff->id.'"', $staffHtml);
        $this->assertStringNotContainsString('value="'.$foreignStaff->id.'"', $staffHtml);
    }

    /**
     * @return array{ClinicContext, int, int}
     */
    private function clinicContextFixture(): array
    {
        $now = now();
        $clinicId = DB::table('clinics')->insertGetId([
            'code' => 'CRT-'.uniqid(),
            'name' => 'Clinical route test',
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $otherClinicId = DB::table('clinics')->insertGetId([
            'code' => 'OTH-'.uniqid(),
            'name' => 'Other clinic',
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return [new ClinicContext(1, $clinicId, 1), $clinicId, $otherClinicId];
    }

    private function createStaff(User $user, int $clinicId, string $specialty): Staff
    {
        $id = DB::table('staff')->insertGetId([
            'clinic_id' => $clinicId,
            'user_id' => $user->id,
            'employee_id' => 'EMP-'.uniqid(),
            'specialty' => $specialty,
            'is_available' => true,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return Staff::query()->findOrFail($id);
    }
}
