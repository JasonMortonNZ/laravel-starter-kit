<?php

declare(strict_types=1);

use App\Models\User;

test('profile page is displayed', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get(route('profile.edit'));

    $response->assertOk();
});

test('profile information can be updated', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->patchJson(route('profile.update'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

    $response
        ->assertOk()
        ->assertJsonPath('toast.message', 'Profile updated.')
        ->assertJsonPath('user.name', 'Test User')
        ->assertJsonPath('user.email_verified_at', null);

    $user->refresh();

    expect($user->name)->toBe('Test User');
    expect($user->email)->toBe('test@example.com');
    expect($user->email_verified_at)->toBeNull();
});

test('email verification status is unchanged when the email address is unchanged', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->patchJson(route('profile.update'), [
            'name' => 'Test User',
            'email' => $user->email,
        ]);

    $response->assertOk();

    expect($user->refresh()->email_verified_at)->not->toBeNull();
});

test('profile information must be valid', function () {
    $user = User::factory()->create();

    $this
        ->actingAs($user)
        ->patchJson(route('profile.update'), [
            'name' => '',
            'email' => 'not-an-email',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['name', 'email']);
});

test('user can delete their account', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->deleteJson(route('profile.destroy'), [
            'password' => 'password',
        ]);

    $response
        ->assertOk()
        ->assertJsonPath('redirect', '/');

    $this->assertGuest();
    expect($user->fresh())->toBeNull();
});

test('correct password must be provided to delete account', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->deleteJson(route('profile.destroy'), [
            'password' => 'wrong-password',
        ]);

    $response
        ->assertUnprocessable()
        ->assertJsonValidationErrors('password');

    expect($user->fresh())->not->toBeNull();
});
