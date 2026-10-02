<?php

declare(strict_types=1);

use App\Configurables\PasswordDefaults;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

afterEach(function (): void {
    app()->detectEnvironment(fn (): string => 'testing');

    (new PasswordDefaults)->configure();
});

test('production requires strong passwords', function (): void {
    app()->detectEnvironment(fn (): string => 'production');

    (new PasswordDefaults)->configure();

    $validator = Validator::make(['password' => 'password'], ['password' => Password::default()]);

    expect($validator->fails())->toBeTrue();
});

test('other environments use the framework default', function (): void {
    (new PasswordDefaults)->configure();

    $validator = Validator::make(['password' => 'password'], ['password' => Password::default()]);

    expect($validator->passes())->toBeTrue();
});
