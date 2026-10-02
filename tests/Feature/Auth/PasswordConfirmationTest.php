<?php

declare(strict_types=1);

use App\Models\User;

test('confirm password screen can be rendered', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('password.confirm'));

    $response->assertOk();
});

test('password confirmation requires authentication', function () {
    $response = $this->get(route('password.confirm'));

    $response->assertRedirect(route('login'));
});

test('password can be confirmed', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->postJson(route('password.confirm.store'), ['password' => 'password'])
        ->assertCreated()
        ->assertSessionHas('auth.password_confirmed_at');
});

test('password is not confirmed with an invalid password', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->postJson(route('password.confirm.store'), ['password' => 'wrong-password'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('password');
});
