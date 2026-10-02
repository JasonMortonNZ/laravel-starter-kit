<?php

declare(strict_types=1);

use App\Enums\TeamRole;
use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

test('any user can list and create teams', function (): void {
    $user = User::factory()->create();

    expect(Gate::forUser($user)->allows('viewAny', Team::class))->toBeTrue()
        ->and(Gate::forUser($user)->allows('create', Team::class))->toBeTrue();
});

test('only members can view a team', function (): void {
    $member = User::factory()->create();
    $outsider = User::factory()->create();
    $team = Team::factory()->create();

    $team->members()->attach($member, ['role' => TeamRole::Member->value]);

    expect(Gate::forUser($member)->allows('view', $team))->toBeTrue()
        ->and(Gate::forUser($outsider)->allows('view', $team))->toBeFalse();
});

test('only owners and admins can add members', function (): void {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $team = Team::factory()->create();

    $team->members()->attach($owner, ['role' => TeamRole::Owner->value]);
    $team->members()->attach($member, ['role' => TeamRole::Member->value]);

    expect(Gate::forUser($owner)->allows('addMember', $team))->toBeTrue()
        ->and(Gate::forUser($member)->allows('addMember', $team))->toBeFalse();
});
