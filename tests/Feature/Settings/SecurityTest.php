<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Laravel\Fortify\Features;

test('security settings are returned', function () {
    $this->skipUnlessFortifyHas(Features::twoFactorAuthentication());

    Features::twoFactorAuthentication([
        'confirm' => true,
        'confirmPassword' => true,
    ]);

    $user = User::factory()->create();

    $this->actingAs($user)
        ->withSession(['auth.password_confirmed_at' => time()])
        ->getJson(route('api.settings.security'))
        ->assertOk()
        ->assertJsonPath('canManageTwoFactor', true)
        ->assertJsonPath('twoFactorEnabled', false)
        ->assertJsonPath('requiresConfirmation', true)
        ->assertJsonPath('passwordRules', fn (mixed $rules) => is_string($rules));
});

test('security page requires password confirmation when enabled', function () {
    $this->skipUnlessFortifyHas(Features::twoFactorAuthentication());

    $user = User::factory()->create();

    Features::twoFactorAuthentication([
        'confirm' => true,
        'confirmPassword' => true,
    ]);

    $this->actingAs($user)
        ->get(route('security.edit'))
        ->assertRedirect(route('password.confirm'));

    $this->actingAs($user)
        ->getJson(route('api.settings.security'))
        ->assertStatus(423);
});

test('security settings omit two factor when feature is disabled', function () {
    $this->skipUnlessFortifyHas(Features::twoFactorAuthentication());

    config(['fortify.features' => []]);

    $user = User::factory()->create();

    $this->actingAs($user)
        ->withSession(['auth.password_confirmed_at' => time()])
        ->getJson(route('api.settings.security'))
        ->assertOk()
        ->assertJsonPath('canManageTwoFactor', false)
        ->assertJsonMissingPath('twoFactorEnabled')
        ->assertJsonMissingPath('requiresConfirmation');
});

test('password can be updated', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->putJson(route('user-password.update'), [
            'current_password' => 'password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

    $response
        ->assertOk()
        ->assertJsonPath('toast.message', 'Password updated.');

    expect(Hash::check('new-password', $user->refresh()->password))->toBeTrue();
});

test('correct password must be provided to update password', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->putJson(route('user-password.update'), [
            'current_password' => 'wrong-password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

    $response
        ->assertUnprocessable()
        ->assertJsonValidationErrors('current_password');
});
