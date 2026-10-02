<?php

declare(strict_types=1);

use App\Models\User;
use Laravel\Fortify\Features;

beforeEach(function () {
    $this->skipUnlessFortifyHas(Features::twoFactorAuthentication());

    Features::twoFactorAuthentication([
        'confirm' => true,
        'confirmPassword' => true,
    ]);
});

test('two factor challenge redirects to login when not authenticated', function () {
    $response = $this->get(route('two-factor.login'));

    $response->assertRedirect(route('login'));
});

test('two factor challenge can be rendered after a password login', function () {
    $user = User::factory()->withTwoFactor()->create();

    $this->postJson(route('login.store'), [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->get(route('two-factor.login'))->assertOk();
});

test('users can complete the two factor challenge with a recovery code', function () {
    $user = User::factory()->withTwoFactor()->create();

    $this->postJson(route('login.store'), [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response = $this->postJson(route('two-factor.login.store'), [
        'recovery_code' => 'recovery-code-1',
    ]);

    $this->assertAuthenticatedAs($user);
    $response
        ->assertOk()
        ->assertJsonPath('two_factor', false)
        ->assertJsonPath('redirect', "/{$user->personalTeam()->slug}/dashboard");
});

test('users cannot complete the two factor challenge with an invalid recovery code', function () {
    $user = User::factory()->withTwoFactor()->create();

    $this->postJson(route('login.store'), [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->postJson(route('two-factor.login.store'), [
        'recovery_code' => 'not-a-real-code',
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('recovery_code');

    $this->assertGuest();
});
