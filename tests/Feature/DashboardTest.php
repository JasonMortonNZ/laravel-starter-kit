<?php

declare(strict_types=1);

use App\Enums\TeamRole;
use App\Models\Team;
use App\Models\TeamInvitation;
use App\Models\User;

test('guests are redirected to the login page', function (): void {
    User::factory()->create();

    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login', ['redirect' => route('dashboard', absolute: false)]));
});

test('authenticated users can visit the dashboard', function (): void {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get(route('dashboard'));

    $response->assertOk();
});

test('dashboard data includes the current team and pending invitations', function (): void {
    $owner = User::factory()->create(['name' => 'Taylor Otwell']);
    $invitedUser = User::factory()->create(['email' => 'invited@example.com']);
    $team = Team::factory()->create(['name' => 'Laravel Team']);

    $team->members()->attach($owner, ['role' => TeamRole::Owner->value]);

    $invitation = TeamInvitation::factory()->create([
        'team_id' => $team->id,
        'email' => 'invited@example.com',
        'invited_by' => $owner->id,
    ]);

    $response = $this
        ->actingAs($invitedUser)
        ->getJson(route('api.dashboard'));

    $response
        ->assertOk()
        ->assertJsonPath('current_team.slug', $invitedUser->personalTeam()->slug)
        ->assertJsonCount(1, 'pending_invitations')
        ->assertJsonPath('pending_invitations.0.code', $invitation->code)
        ->assertJsonPath('pending_invitations.0.inviter_name', 'Taylor Otwell')
        ->assertJsonPath('pending_invitations.0.team.name', 'Laravel Team')
        ->assertJsonPath('pending_invitations.0.team.slug', $team->slug)
        ->assertJsonMissingPath('pending_invitations.0.team_name');
});

test('dashboard data does not include accepted invitations', function (): void {
    $owner = User::factory()->create();
    $invitedUser = User::factory()->create(['email' => 'invited@example.com']);
    $team = Team::factory()->create();

    $team->members()->attach($owner, ['role' => TeamRole::Owner->value]);

    TeamInvitation::factory()->accepted()->create([
        'team_id' => $team->id,
        'email' => 'invited@example.com',
        'invited_by' => $owner->id,
    ]);

    $this
        ->actingAs($invitedUser)
        ->getJson(route('api.dashboard'))
        ->assertOk()
        ->assertJsonCount(0, 'pending_invitations');
});

test('dashboard data excludes expired invitations without deleting them', function (): void {
    $owner = User::factory()->create();
    $invitedUser = User::factory()->create(['email' => 'invited@example.com']);
    $team = Team::factory()->create();

    $team->members()->attach($owner, ['role' => TeamRole::Owner->value]);

    $invitation = TeamInvitation::factory()->expired()->create([
        'team_id' => $team->id,
        'email' => 'invited@example.com',
        'invited_by' => $owner->id,
    ]);

    $this
        ->actingAs($invitedUser)
        ->getJson(route('api.dashboard'))
        ->assertOk()
        ->assertJsonCount(0, 'pending_invitations');

    $this->assertDatabaseHas('team_invitations', [
        'id' => $invitation->id,
    ]);
});

test('dashboard data does not include or delete other users invitations', function (): void {
    $owner = User::factory()->create();
    $invitedUser = User::factory()->create(['email' => 'invited@example.com']);
    $team = Team::factory()->create();

    $team->members()->attach($owner, ['role' => TeamRole::Owner->value]);

    $invitation = TeamInvitation::factory()->expired()->create([
        'team_id' => $team->id,
        'email' => 'someone@example.com',
        'invited_by' => $owner->id,
    ]);

    $this
        ->actingAs($invitedUser)
        ->getJson(route('api.dashboard'))
        ->assertOk()
        ->assertJsonCount(0, 'pending_invitations');

    $this->assertDatabaseHas('team_invitations', [
        'id' => $invitation->id,
    ]);
});

test('dashboard data requires authentication', function (): void {
    User::factory()->create();

    $this->getJson(route('api.dashboard'))->assertUnauthorized();
});
