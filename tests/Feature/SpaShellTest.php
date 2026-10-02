<?php

declare(strict_types=1);

test('unknown paths serve the application shell', function () {
    $this->get('/some/client/side/route')
        ->assertOk()
        ->assertSee('id="app"', false);
});

test('unknown api paths are not found', function () {
    $this->getJson('/api/nope')
        ->assertNotFound()
        ->assertJsonStructure(['message']);
});

test('the health check is not swallowed by the shell', function () {
    $this->get('/up')->assertOk();
});

test('framework required route names resolve to shell routes', function () {
    expect(route('login', absolute: false))->toBe('/login')
        ->and(route('register', absolute: false))->toBe('/register')
        ->and(route('password.request', absolute: false))->toBe('/forgot-password')
        ->and(route('password.reset', ['token' => 'abc'], false))->toBe('/reset-password/abc')
        ->and(route('verification.notice', absolute: false))->toBe('/email/verify')
        ->and(route('password.confirm', absolute: false))->toBe('/user/confirm-password')
        ->and(route('two-factor.login', absolute: false))->toBe('/two-factor-challenge');
});
