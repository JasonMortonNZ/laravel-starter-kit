<?php

declare(strict_types=1);

use App\Models\User;

test('guests receive the public application state', function () {
    $response = $this->getJson(route('api.bootstrap'));

    $response
        ->assertOk()
        ->assertJsonPath('name', config('app.name'))
        ->assertJsonPath('auth.user', null)
        ->assertJsonPath('currentTeam', null)
        ->assertJsonCount(0, 'teams')
        ->assertJsonPath('features.canRegister', true)
        ->assertJsonPath('features.canResetPassword', true)
        ->assertJsonPath('features.mustVerifyEmail', false)
        ->assertJsonPath('passwordRules', fn (mixed $rules) => is_string($rules));
});

test('authenticated users receive their profile and teams', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->getJson(route('api.bootstrap'));

    $response
        ->assertOk()
        ->assertJsonPath('auth.user.id', $user->id)
        ->assertJsonPath('auth.user.email', $user->email)
        ->assertJsonPath('auth.user.two_factor_enabled', false)
        ->assertJsonMissingPath('auth.user.password')
        ->assertJsonPath('currentTeam.slug', $user->personalTeam()->slug)
        ->assertJsonPath('currentTeam.isCurrent', true)
        ->assertJsonCount(1, 'teams')
        ->assertJsonPath('features.mustVerifyEmail', true)
        ->assertJsonPath('features.canManageTwoFactor', true);
});
