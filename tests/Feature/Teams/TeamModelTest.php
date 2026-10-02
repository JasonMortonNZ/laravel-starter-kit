<?php

declare(strict_types=1);

use App\Enums\TeamRole;
use App\Models\Team;
use App\Models\TeamInvitation;
use App\Models\User;

test('memberships belong to a team and a user', function (): void {
    $user = User::factory()->create();
    $team = Team::factory()->create();

    $team->members()->attach($user, ['role' => TeamRole::Member->value]);

    $membership = $team->memberships()->firstOrFail();

    expect($membership->team->is($team))->toBeTrue()
        ->and($membership->user->is($user))->toBeTrue();
});

test('users cannot switch to a team they do not belong to', function (): void {
    $user = User::factory()->create();
    $team = Team::factory()->create();

    expect($user->switchTeam($team))->toBeFalse()
        ->and($user->fresh()->current_team_id)->toBe($user->personalTeam()->id);
});

test('team slugs only get a suffix when the slug is taken', function (): void {
    Team::factory()->create(['name' => 'Acme Corp', 'slug' => null]);

    $acme = Team::factory()->create(['name' => 'Acme', 'slug' => null]);
    $secondAcme = Team::factory()->create(['name' => 'Acme', 'slug' => null]);

    expect($acme->slug)->toBe('acme')
        ->and($secondAcme->slug)->toBe('acme-1');
});

test('invitations are pending until accepted or expired', function (): void {
    expect(TeamInvitation::factory()->create()->isPending())->toBeTrue()
        ->and(TeamInvitation::factory()->accepted()->create()->isPending())->toBeFalse()
        ->and(TeamInvitation::factory()->expired()->create()->isPending())->toBeFalse();
});

test('email addresses are stored lowercased', function (): void {
    $user = User::factory()->create(['email' => 'Taylor@Example.COM']);
    $invitation = TeamInvitation::factory()->create(['email' => 'Abigail@Example.COM']);

    expect($user->email)->toBe('taylor@example.com')
        ->and($invitation->email)->toBe('abigail@example.com');

    $this->assertDatabaseHas('users', ['email' => 'taylor@example.com']);
    $this->assertDatabaseHas('team_invitations', ['email' => 'abigail@example.com']);
});
