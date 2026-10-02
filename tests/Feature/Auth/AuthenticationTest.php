<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Support\Facades\RateLimiter;
use Laravel\Fortify\Features;

test('login screen can be rendered', function (): void {
    $response = $this->get(route('login'));

    $response->assertOk();
});

test('authenticated users are redirected away from the login screen', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('login'))
        ->assertRedirect();
});

test('users can authenticate using the login screen', function (): void {
    $user = User::factory()->create();

    $response = $this->postJson(route('login.store'), [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    $response
        ->assertOk()
        ->assertJsonPath('two_factor', false);
});

test('guests visiting a guarded page are sent to login with the intended path', function (): void {
    $this->get('/settings/profile?tab=details')
        ->assertRedirect(route('login', ['redirect' => '/settings/profile?tab=details']));
});

test('users with two factor enabled are asked for a two factor code', function (): void {
    if (! Features::canManageTwoFactorAuthentication()) {
        $this->markTestSkipped('Two-factor authentication is not enabled.');
    }

    Features::twoFactorAuthentication([
        'confirm' => true,
        'confirmPassword' => true,
    ]);

    $user = User::factory()->withTwoFactor()->create();

    $response = $this->postJson(route('login.store'), [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response
        ->assertOk()
        ->assertJsonPath('two_factor', true)
        ->assertSessionHas('login.id', $user->id);
    $this->assertGuest();
});

test('users can not authenticate with invalid password', function (): void {
    $user = User::factory()->create();

    $this->postJson(route('login.store'), [
        'email' => $user->email,
        'password' => 'wrong-password',
    ])->assertUnprocessable();

    $this->assertGuest();
});

test('users can logout', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson(route('logout'));

    $this->assertGuest();
    $response->assertNoContent();
});

test('users are rate limited', function (): void {
    $user = User::factory()->create();

    RateLimiter::increment(md5('login'.implode('|', [$user->email, '127.0.0.1'])), amount: 5);

    $response = $this->postJson(route('login.store'), [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    $response->assertTooManyRequests();
});
