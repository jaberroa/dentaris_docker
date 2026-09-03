<?php

namespace Tests\Feature\Clinics;

use App\Http\Middleware\ResolveClinicContext;
use App\Models\User;
use App\Modules\Clinics\Data\ClinicContext;
use App\Modules\Clinics\Services\ClinicContextResolver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Session\ArraySessionHandler;
use Illuminate\Session\Store;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class ClinicContextHardeningTest extends TestCase
{
    use RefreshDatabase;

    public function test_resolver_rejects_an_inactive_user(): void
    {
        $fixture = $this->activeFixture();
        $fixture['user']->update(['is_active' => false]);

        $this->assertNull($this->resolve($fixture));
    }

    public function test_resolver_rejects_an_active_membership_that_was_not_activated(): void
    {
        $fixture = $this->activeFixture();
        DB::table('clinic_memberships')
            ->where('id', $fixture['membership_id'])
            ->update(['activated_at' => null]);

        $this->assertNull($this->resolve($fixture));
    }

    public function test_resolver_rejects_an_active_membership_with_a_suspension_date(): void
    {
        $fixture = $this->activeFixture();
        DB::table('clinic_memberships')
            ->where('id', $fixture['membership_id'])
            ->update(['suspended_at' => now()]);

        $this->assertNull($this->resolve($fixture));
    }

    public function test_middleware_preserves_header_transport_and_exposes_the_validated_context(): void
    {
        $fixture = $this->activeFixture();
        $request = $this->requestFor($fixture['user'], [
            'X-Clinic-Id' => $fixture['clinic_id'],
            'X-Clinic-Site-Id' => $fixture['site_id'],
        ]);

        $response = app(ResolveClinicContext::class)->handle(
            $request,
            function (Request $request) use ($fixture): Response {
                $context = $request->attributes->get(ClinicContext::class);

                $this->assertInstanceOf(ClinicContext::class, $context);
                $this->assertSame($fixture['clinic_id'], $context->clinicId);
                $this->assertSame($fixture['membership_id'], $context->membershipId);
                $this->assertSame($fixture['site_id'], $context->clinicSiteId);
                $this->assertSame($context, $request->attributes->get('clinic.context'));

                return response('', 204);
            }
        );

        $this->assertSame(204, $response->getStatusCode());
    }

    public function test_middleware_uses_web_session_transport_when_headers_are_absent(): void
    {
        $fixture = $this->activeFixture();
        $request = $this->requestFor($fixture['user'], session: [
            'clinic_id' => $fixture['clinic_id'],
            'clinic_site_id' => $fixture['site_id'],
        ]);

        $response = app(ResolveClinicContext::class)->handle(
            $request,
            function (Request $request) use ($fixture): Response {
                $context = $request->attributes->get(ClinicContext::class);

                $this->assertInstanceOf(ClinicContext::class, $context);
                $this->assertSame($fixture['clinic_id'], $context->clinicId);
                $this->assertSame($fixture['site_id'], $context->clinicSiteId);

                return response('', 204);
            }
        );

        $this->assertSame(204, $response->getStatusCode());
    }

    public function test_an_invalid_header_does_not_fall_back_to_a_valid_session_context(): void
    {
        $fixture = $this->activeFixture();
        $request = $this->requestFor(
            $fixture['user'],
            ['X-Clinic-Id' => 'invalid'],
            ['clinic_id' => $fixture['clinic_id']]
        );
        $request->headers->set('Accept', 'application/json');

        $response = app(ResolveClinicContext::class)->handle(
            $request,
            fn (): Response => response('', 204)
        );

        $this->assertSame(403, $response->getStatusCode());
        $this->assertSame('CLINIC_CONTEXT_UNAVAILABLE', $response->getData(true)['code']);
    }

    /**
     * @return array{user: User, clinic_id: int, membership_id: int, site_id: int}
     */
    private function activeFixture(): array
    {
        $now = now();
        $user = User::factory()->create(['is_active' => true]);
        $clinicId = DB::table('clinics')->insertGetId([
            'name' => 'Clínica Contexto Seguro '.uniqid(),
            'code' => 'CTX-'.uniqid(),
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
        $siteId = DB::table('clinic_sites')->insertGetId([
            'clinic_id' => $clinicId,
            'name' => 'Sede Contexto Seguro '.uniqid(),
            'code' => 'SITE-'.uniqid(),
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('clinic_membership_sites')->insert([
            'clinic_membership_id' => $membershipId,
            'clinic_site_id' => $siteId,
            'clinic_id' => $clinicId,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return [
            'user' => $user,
            'clinic_id' => $clinicId,
            'membership_id' => $membershipId,
            'site_id' => $siteId,
        ];
    }

    /**
     * @param  array{user: User, clinic_id: int, membership_id: int, site_id: int}  $fixture
     */
    private function resolve(array $fixture): ?ClinicContext
    {
        return app(ClinicContextResolver::class)->resolve(
            $fixture['user']->id,
            $fixture['clinic_id'],
            $fixture['site_id'],
        );
    }

    /**
     * @param  array<string, int|string>  $headers
     * @param  array<string, int|string>  $session
     */
    private function requestFor(User $user, array $headers = [], array $session = []): Request
    {
        $request = Request::create('/clinic-context-hardening', 'GET');
        $request->setUserResolver(fn (): User => $user);

        foreach ($headers as $name => $value) {
            $request->headers->set($name, (string) $value);
        }

        if ($session !== []) {
            $store = new Store('clinic-context-hardening', new ArraySessionHandler(120));
            $store->start();
            $store->put($session);
            $request->setLaravelSession($store);
        }

        return $request;
    }
}
