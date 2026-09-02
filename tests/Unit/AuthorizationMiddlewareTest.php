<?php

namespace Tests\Unit;

use App\Http\Middleware\CheckPermission;
use App\Http\Middleware\CheckRole;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class AuthorizationMiddlewareTest extends TestCase
{
    public function test_permission_middleware_allows_authorized_user(): void
    {
        $user = $this->createMock(User::class);
        $user->method('hasPermission')->with('manage_billing')->willReturn(true);

        Auth::shouldReceive('check')->once()->andReturn(true);
        Auth::shouldReceive('user')->once()->andReturn($user);

        $response = (new CheckPermission())->handle(
            Request::create('/billing', 'POST'),
            fn () => new Response('allowed'),
            'manage_billing'
        );

        $this->assertSame('allowed', $response->getContent());
        $this->assertSame(200, $response->getStatusCode());
    }

    public function test_permission_middleware_rejects_unauthorized_json_request(): void
    {
        $user = $this->createMock(User::class);
        $user->method('hasPermission')->with('manage_billing')->willReturn(false);

        Auth::shouldReceive('check')->once()->andReturn(true);
        Auth::shouldReceive('user')->once()->andReturn($user);

        $request = Request::create('/billing', 'POST', [], [], [], [
            'HTTP_ACCEPT' => 'application/json',
        ]);
        $response = (new CheckPermission())->handle($request, fn () => new Response('allowed'), 'manage_billing');

        $this->assertSame(403, $response->getStatusCode());
        $this->assertSame('Unauthorized', $response->getData(true)['error']);
    }

    public function test_permission_middleware_redirects_guest(): void
    {
        Auth::shouldReceive('check')->once()->andReturn(false);

        $response = (new CheckPermission())->handle(
            Request::create('/billing', 'POST'),
            fn () => new Response('allowed'),
            'manage_billing'
        );

        $this->assertSame(302, $response->getStatusCode());
        $this->assertStringContainsString('/login', $response->headers->get('Location'));
    }

    public function test_role_middleware_allows_user_with_any_required_role(): void
    {
        $user = $this->createMock(User::class);
        $user->method('hasAnyRole')->with(['inventory_manager', 'admin'])->willReturn(true);

        Auth::shouldReceive('check')->once()->andReturn(true);
        Auth::shouldReceive('user')->once()->andReturn($user);

        $response = (new CheckRole())->handle(
            Request::create('/inventory', 'GET'),
            fn () => new Response('allowed'),
            'inventory_manager',
            'admin'
        );

        $this->assertSame(200, $response->getStatusCode());
    }
}
