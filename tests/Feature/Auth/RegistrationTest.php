<?php

declare(strict_types=1);

use App\Models\User;

test('registration screen can be rendered', function (): void {
    $response = $this->get(route('register'));

    $response->assertOk();
});

test('new users can register', function (): void {
    $response = $this->postJson(route('register.store'), [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertAuthenticated();

    $user = User::query()->where('email', 'test@example.com')->firstOrFail();

    $response
        ->assertCreated()
        ->assertJsonPath('two_factor', false);
});

test('registration requires valid input', function (): void {
    $this->postJson(route('register.store'), [
        'name' => 'Test User',
        'email' => 'not-an-email',
        'password' => 'password',
        'password_confirmation' => 'different',
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['email', 'password']);

    $this->assertGuest();
});
