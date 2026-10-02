<?php

declare(strict_types=1);

use App\Models\User;

test('guests receive the public application state', function (): void {
    $response = $this->getJson(route('api.bootstrap'));

    $response
        ->assertOk()
        ->assertJsonPath('name', config('app.name'))
        ->assertJsonPath('auth.user', null)
        ->assertJsonPath('current_team', null)
        ->assertJsonCount(0, 'teams')
        ->assertJsonPath('features.can_register', true)
        ->assertJsonPath('features.can_reset_password', true)
        ->assertJsonPath('features.must_verify_email', false)
        ->assertJsonPath('password_rules', fn (mixed $rules): bool => is_string($rules));
});

test('authenticated users receive their profile and teams', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->getJson(route('api.bootstrap'));

    $response
        ->assertOk()
        ->assertJsonPath('auth.user.id', $user->id)
        ->assertJsonPath('auth.user.email', $user->email)
        ->assertJsonPath('auth.user.two_factor_enabled', false)
        ->assertJsonMissingPath('auth.user.password')
        ->assertJsonPath('current_team.slug', $user->personalTeam()->slug)
        ->assertJsonPath('current_team.is_current', true)
        ->assertJsonCount(1, 'teams')
        ->assertJsonPath('features.must_verify_email', true)
        ->assertJsonPath('features.can_manage_two_factor', true);
});
