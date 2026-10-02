<?php

declare(strict_types=1);

use App\Enums\TeamRole;
use App\Models\Team;
use App\Models\TeamInvitation;
use App\Models\User;

function invitationFor(Team $team, User $owner, string $state = 'pending'): TeamInvitation
{
    $factory = TeamInvitation::factory();

    if ($state !== 'pending') {
        $factory = $factory->{$state}();
    }

    return $factory->create([
        'team_id' => $team->id,
        'email' => 'invited@example.com',
        'invited_by' => $owner->id,
    ]);
}

beforeEach(function () {
    $this->owner = User::factory()->create();
    $this->team = Team::factory()->create(['name' => 'Laravel Team']);
    $this->team->members()->attach($this->owner, ['role' => TeamRole::Owner->value]);
});

test('a pending invitation code resolves to its team', function () {
    $invitation = invitationFor($this->team, $this->owner);

    $this->getJson(route('api.auth.invitation', ['code' => $invitation->code]))
        ->assertOk()
        ->assertJsonPath('code', $invitation->code)
        ->assertJsonPath('teamName', 'Laravel Team');
});

test('a missing code is not found', function () {
    $this->getJson(route('api.auth.invitation'))->assertNotFound();
});

test('an unknown code is not found', function () {
    $this->getJson(route('api.auth.invitation', ['code' => 'nope']))->assertNotFound();
});

test('an accepted invitation is not found', function () {
    $invitation = invitationFor($this->team, $this->owner, 'accepted');

    $this->getJson(route('api.auth.invitation', ['code' => $invitation->code]))->assertNotFound();
});

test('an expired invitation is not found', function () {
    $invitation = invitationFor($this->team, $this->owner, 'expired');

    $this->getJson(route('api.auth.invitation', ['code' => $invitation->code]))->assertNotFound();
});
