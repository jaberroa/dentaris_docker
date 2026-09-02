<?php

namespace Tests\Feature\Clinics;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Contrato QA para la fundación multiclínica.
 *
 * La implementación debe exponer estos puntos públicos:
 * - App\Modules\Clinics\Services\ClinicContextResolver::resolve(
 *       int $userId,
 *       ?int $clinicId,
 *       ?int $clinicSiteId = null,
 *   ): ?App\Modules\Clinics\Data\ClinicContext
 * - ClinicContext::$clinicId, ClinicContext::$membershipId y
 *   ClinicContext::$clinicSiteId como valores inmutables.
 *
 * Las tablas de la fundación son parte del mismo contrato:
 * clinics, clinic_memberships, clinic_sites y clinic_membership_sites.
 */
class ClinicContextContractTest extends TestCase
{
    use RefreshDatabase;

    private const RESOLVER = 'App\\Modules\\Clinics\\Services\\ClinicContextResolver';

    private const CONTEXT = 'App\\Modules\\Clinics\\Data\\ClinicContext';

    protected function setUp(): void
    {
        parent::setUp();

        $this->requireContextContract();
    }

    public function test_it_rejects_an_absent_clinic_context(): void
    {
        $this->assertNull($this->resolve(1, null));
    }

    public function test_it_rejects_an_inactive_clinic_even_when_the_membership_is_active(): void
    {
        $fixture = $this->activeFixture();

        DB::table('clinics')
            ->where('id', $fixture['clinic_id'])
            ->update(['is_active' => false]);

        $this->assertNull($this->resolve($fixture['user_id'], $fixture['clinic_id']));
    }

    public function test_it_rejects_an_inactive_membership_even_when_the_clinic_is_active(): void
    {
        $fixture = $this->activeFixture();

        DB::table('clinic_memberships')
            ->where('id', $fixture['membership_id'])
            ->update(['status' => 'suspended', 'suspended_at' => now()]);

        $this->assertNull($this->resolve($fixture['user_id'], $fixture['clinic_id']));
    }

    public function test_it_rejects_a_branch_that_is_not_authorized_for_the_active_membership(): void
    {
        $fixture = $this->activeFixture(withAuthorizedSite: false);

        $this->assertNull($this->resolve($fixture['user_id'], $fixture['clinic_id'], $fixture['site_id']));
    }

    public function test_the_clinic_context_value_isolates_records_to_its_selected_clinic(): void
    {
        $fixture = $this->activeFixture();
        $foreignClinicId = $this->createClinic(isActive: true);
        $context = $this->resolve($fixture['user_id'], $fixture['clinic_id'], $fixture['site_id']);

        $this->assertInstanceOf(self::CONTEXT, $context);

        DB::statement(
            'create table clinic_context_contract_records ('
            .'id integer primary key autoincrement, '
            .'clinic_id integer not null, '
            .'label varchar(100) not null'
            .')'
        );

        DB::table('clinic_context_contract_records')->insert([
            ['clinic_id' => $fixture['clinic_id'], 'label' => 'registro-clinica-seleccionada'],
            ['clinic_id' => $foreignClinicId, 'label' => 'registro-clinica-ajena'],
        ]);

        $visibleLabels = DB::table('clinic_context_contract_records')
            ->where('clinic_id', $context->clinicId)
            ->orderBy('id')
            ->pluck('label')
            ->all();

        $this->assertSame($fixture['clinic_id'], $context->clinicId);
        $this->assertSame($fixture['membership_id'], $context->membershipId);
        $this->assertSame($fixture['site_id'], $context->clinicSiteId);
        $this->assertSame(['registro-clinica-seleccionada'], $visibleLabels);
        $this->assertNotContains('registro-clinica-ajena', $visibleLabels);
    }

    /**
     * @return array{user_id: int, clinic_id: int, membership_id: int, site_id: int}
     */
    private function activeFixture(bool $withAuthorizedSite = true): array
    {
        $now = now();
        $userId = DB::table('users')->insertGetId([
            'name' => 'QA Clinic Context',
            'email' => 'qa-clinic-context-'.uniqid().'@example.test',
            'password' => bcrypt('qa-contract-only'),
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $clinicId = $this->createClinic(isActive: true);
        $membershipId = DB::table('clinic_memberships')->insertGetId([
            'user_id' => $userId,
            'clinic_id' => $clinicId,
            'status' => 'active',
            'activated_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $siteId = DB::table('clinic_sites')->insertGetId([
            'clinic_id' => $clinicId,
            'name' => 'Sede QA',
            'code' => 'QA-'.uniqid(),
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        if ($withAuthorizedSite) {
            DB::table('clinic_membership_sites')->insert([
                'clinic_membership_id' => $membershipId,
                'clinic_site_id' => $siteId,
                'clinic_id' => $clinicId,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        return [
            'user_id' => $userId,
            'clinic_id' => $clinicId,
            'membership_id' => $membershipId,
            'site_id' => $siteId,
        ];
    }

    private function createClinic(bool $isActive): int
    {
        $now = now();

        return DB::table('clinics')->insertGetId([
            'name' => 'Clínica QA '.uniqid(),
            'code' => 'CL-'.uniqid(),
            'is_active' => $isActive,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    private function resolve(int $userId, ?int $clinicId, ?int $clinicSiteId = null): ?object
    {
        return app(self::RESOLVER)->resolve($userId, $clinicId, $clinicSiteId);
    }

    private function requireContextContract(): void
    {
        $this->assertTrue(
            class_exists(self::RESOLVER),
            'Falta ClinicContextResolver; esta prueba define el contrato QA de la fundación multiclínica.'
        );
        $this->assertTrue(class_exists(self::CONTEXT), 'Falta el valor inmutable ClinicContext.');
        $this->assertTrue(
            method_exists(self::RESOLVER, 'resolve'),
            'ClinicContextResolver debe exponer resolve(int $userId, ?int $clinicId, ?int $clinicSiteId = null).'
        );
    }
}
