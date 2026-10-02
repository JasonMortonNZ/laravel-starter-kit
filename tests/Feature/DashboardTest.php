<?php

declare(strict_types=1);

use App\Enums\TeamRole;
use App\Models\Team;
use App\Models\TeamInvitation;
use App\Models\User;

test('guests are redirected to the login page', function () {
    User::factory()->create();

    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('authenticated users can visit the dashboard', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get(route('dashboard'));

    $response->assertOk();
});

test('dashboard data includes the current team and pending invitations', function () {
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
        ->assertJsonPath('currentTeam.slug', $invitedUser->personalTeam()->slug)
        ->assertJsonCount(1, 'pendingInvitations')
        ->assertJsonPath('pendingInvitations.0.code', $invitation->code)
        ->assertJsonPath('pendingInvitations.0.inviterName', 'Taylor Otwell')
        ->assertJsonPath('pendingInvitations.0.team.name', 'Laravel Team')
        ->assertJsonPath('pendingInvitations.0.team.slug', $team->slug)
        ->assertJsonMissingPath('pendingInvitations.0.teamName');
});

test('dashboard data does not include accepted invitations', function () {
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
        ->assertJsonCount(0, 'pendingInvitations');
});

test('dashboard data excludes expired invitations without deleting them', function () {
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
        ->assertJsonCount(0, 'pendingInvitations');

    $this->assertDatabaseHas('team_invitations', [
        'id' => $invitation->id,
    ]);
});

test('dashboard data does not include or delete other users invitations', function () {
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
        ->assertJsonCount(0, 'pendingInvitations');

    $this->assertDatabaseHas('team_invitations', [
        'id' => $invitation->id,
    ]);
});

test('dashboard data requires authentication', function () {
    User::factory()->create();

    $this->getJson(route('api.dashboard'))->assertUnauthorized();
});
