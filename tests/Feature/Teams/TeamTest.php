<?php

declare(strict_types=1);

use App\Enums\TeamRole;
use App\Models\Team;
use App\Models\TeamInvitation;
use App\Models\User;

test('the teams index page can be rendered', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get(route('teams.index'));

    $response->assertOk();

    $this
        ->actingAs($user)
        ->getJson(route('api.teams.index'))
        ->assertOk()
        ->assertJsonCount(1, 'teams')
        ->assertJsonPath('teams.0.slug', $user->personalTeam()->slug)
        ->assertJsonPath('teams.0.isCurrent', true);
});

test('teams can be created', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->postJson(route('teams.store'), [
            'name' => 'Test Team',
        ]);

    $response
        ->assertCreated()
        ->assertJsonPath('toast.message', 'Team created.')
        ->assertJsonPath('team.name', 'Test Team')
        ->assertJsonPath('redirect', route('teams.edit', ['team' => Team::where('name', 'Test Team')->firstOrFail()->slug], false));

    $this->assertDatabaseHas('teams', [
        'name' => 'Test Team',
        'is_personal' => false,
    ]);
});

test('personal team returns the team owned by the user', function () {
    $otherUser = User::factory()->create();
    $user = User::factory()->make();
    $user->save();

    $otherUser->personalTeam()->members()->attach($user, [
        'role' => TeamRole::Member->value,
    ]);

    $personalTeam = Team::factory()->personal()->create();
    $personalTeam->members()->attach($user, [
        'role' => TeamRole::Owner->value,
    ]);

    expect($personalTeam->is($user->personalTeam()))->toBeTrue();
});

test('team slug uses next available suffix', function () {
    $user = User::factory()->create();

    Team::factory()->create(['name' => 'Acme', 'slug' => 'acme']);
    Team::factory()->create(['name' => 'Acme One', 'slug' => 'acme-1']);
    Team::factory()->create(['name' => 'Acme Ten', 'slug' => 'acme-10']);

    $this
        ->actingAs($user)
        ->postJson(route('teams.store'), [
            'name' => 'Acme',
        ]);

    $this->assertDatabaseHas('teams', [
        'name' => 'Acme',
        'slug' => 'acme-11',
    ]);
});

test('the team edit page can be rendered', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create();

    $team->members()->attach($user, ['role' => TeamRole::Owner->value]);

    $this
        ->actingAs($user)
        ->get(route('teams.edit', $team))
        ->assertOk();
});

test('team data includes members, invitations, and permissions', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create();

    $team->members()->attach($user, ['role' => TeamRole::Owner->value]);

    $invitation = TeamInvitation::factory()->create([
        'team_id' => $team->id,
        'email' => 'invited@example.com',
        'role' => TeamRole::Admin,
        'invited_by' => $user->id,
    ]);

    $this
        ->actingAs($user)
        ->getJson(route('api.teams.show', $team))
        ->assertOk()
        ->assertJsonPath('team.slug', $team->slug)
        ->assertJsonPath('team.isPersonal', false)
        ->assertJsonPath('members.0.id', $user->id)
        ->assertJsonPath('members.0.role', TeamRole::Owner->value)
        ->assertJsonPath('members.0.role_label', TeamRole::Owner->label())
        ->assertJsonPath('invitations.0.code', $invitation->code)
        ->assertJsonPath('invitations.0.role_label', TeamRole::Admin->label())
        ->assertJsonPath('permissions.canUpdateTeam', true)
        ->assertJsonPath('availableRoles.0.value', TeamRole::Admin->value);
});

test('team data is forbidden for non members', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create();

    $this
        ->actingAs($user)
        ->getJson(route('api.teams.show', $team))
        ->assertForbidden();
});

test('teams can be updated by owners', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create(['name' => 'Original Name']);

    $team->members()->attach($user, ['role' => TeamRole::Owner->value]);

    $response = $this
        ->actingAs($user)
        ->patchJson(route('teams.update', $team), [
            'name' => 'Updated Name',
        ]);

    $response
        ->assertOk()
        ->assertJsonPath('toast.message', 'Team updated.')
        ->assertJsonPath('team.name', 'Updated Name');

    $this->assertDatabaseHas('teams', [
        'id' => $team->id,
        'name' => 'Updated Name',
    ]);
});

test('teams cannot be updated by members', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $team = Team::factory()->create();

    $team->members()->attach($owner, ['role' => TeamRole::Owner->value]);
    $team->members()->attach($member, ['role' => TeamRole::Member->value]);

    $response = $this
        ->actingAs($member)
        ->patchJson(route('teams.update', $team), [
            'name' => 'Updated Name',
        ]);

    $response->assertForbidden();
});

test('teams can be deleted by owners', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create();

    $team->members()->attach($user, ['role' => TeamRole::Owner->value]);

    $response = $this
        ->actingAs($user)
        ->deleteJson(route('teams.destroy', $team), [
            'name' => $team->name,
        ]);

    $response
        ->assertOk()
        ->assertJsonPath('toast.message', 'Team deleted.')
        ->assertJsonPath('redirect', route('teams.index', absolute: false));

    $this->assertSoftDeleted('teams', [
        'id' => $team->id,
    ]);
});

test('team deletion requires name confirmation', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create();

    $team->members()->attach($user, ['role' => TeamRole::Owner->value]);

    $response = $this
        ->actingAs($user)
        ->deleteJson(route('teams.destroy', $team), [
            'name' => 'Wrong Name',
        ]);

    $response
        ->assertUnprocessable()
        ->assertJsonValidationErrors('name');

    $this->assertDatabaseHas('teams', [
        'id' => $team->id,
        'deleted_at' => null,
    ]);
});

test('deleting current team switches to alphabetically first remaining team', function () {
    $user = User::factory()->create(['name' => 'Mike']);

    $zuluTeam = Team::factory()->create(['name' => 'Zulu Team']);
    $zuluTeam->members()->attach($user, ['role' => TeamRole::Owner->value]);

    $alphaTeam = Team::factory()->create(['name' => 'Alpha Team']);
    $alphaTeam->members()->attach($user, ['role' => TeamRole::Owner->value]);

    $betaTeam = Team::factory()->create(['name' => 'Beta Team']);
    $betaTeam->members()->attach($user, ['role' => TeamRole::Owner->value]);

    $user->update(['current_team_id' => $zuluTeam->id]);

    $response = $this
        ->actingAs($user)
        ->deleteJson(route('teams.destroy', $zuluTeam), [
            'name' => $zuluTeam->name,
        ]);

    $response
        ->assertOk()
        ->assertJsonPath('currentTeam.id', $alphaTeam->id);

    $this->assertSoftDeleted('teams', [
        'id' => $zuluTeam->id,
    ]);

    expect($user->fresh()->current_team_id)->toEqual($alphaTeam->id);
});

test('deleting current team falls back to personal team when alphabetically first', function () {
    $user = User::factory()->create();
    $personalTeam = $user->personalTeam();
    $team = Team::factory()->create(['name' => 'Zulu Team']);
    $team->members()->attach($user, ['role' => TeamRole::Owner->value]);

    $user->update(['current_team_id' => $team->id]);

    $response = $this
        ->actingAs($user)
        ->deleteJson(route('teams.destroy', $team), [
            'name' => $team->name,
        ]);

    $response
        ->assertOk()
        ->assertJsonPath('currentTeam.id', $personalTeam->id);

    $this->assertSoftDeleted('teams', [
        'id' => $team->id,
    ]);

    expect($user->fresh()->current_team_id)->toEqual($personalTeam->id);
});

test('deleting non current team leaves current team unchanged', function () {
    $user = User::factory()->create();
    $personalTeam = $user->personalTeam();
    $team = Team::factory()->create();
    $team->members()->attach($user, ['role' => TeamRole::Owner->value]);

    $user->update(['current_team_id' => $personalTeam->id]);

    $response = $this
        ->actingAs($user)
        ->deleteJson(route('teams.destroy', $team), [
            'name' => $team->name,
        ]);

    $response
        ->assertOk()
        ->assertJsonPath('currentTeam.id', $personalTeam->id);

    $this->assertSoftDeleted('teams', [
        'id' => $team->id,
    ]);

    expect($user->fresh()->current_team_id)->toEqual($personalTeam->id);
});

test('members can leave non personal teams', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $team = Team::factory()->create();

    $team->members()->attach($owner, ['role' => TeamRole::Owner->value]);
    $team->members()->attach($member, ['role' => TeamRole::Member->value]);

    $response = $this
        ->actingAs($member)
        ->deleteJson(route('teams.leave', $team));

    $response
        ->assertOk()
        ->assertJsonPath('redirect', route('teams.index', absolute: false))
        ->assertJsonPath('toast', ['type' => 'success', 'message' => "You left the team \"{$team->name}\""]);

    expect($member->fresh()->belongsToTeam($team))->toBeFalse();
});

test('leaving current team switches to alphabetically first remaining team', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create(['name' => 'Mike']);

    $zuluTeam = Team::factory()->create(['name' => 'Zulu Team']);
    $zuluTeam->members()->attach($owner, ['role' => TeamRole::Owner->value]);
    $zuluTeam->members()->attach($member, ['role' => TeamRole::Member->value]);

    $alphaTeam = Team::factory()->create(['name' => 'Alpha Team']);
    $alphaTeam->members()->attach($member, ['role' => TeamRole::Member->value]);

    $betaTeam = Team::factory()->create(['name' => 'Beta Team']);
    $betaTeam->members()->attach($member, ['role' => TeamRole::Member->value]);

    $member->update(['current_team_id' => $zuluTeam->id]);

    $response = $this
        ->actingAs($member)
        ->deleteJson(route('teams.leave', $zuluTeam));

    $response
        ->assertOk()
        ->assertJsonPath('currentTeam.id', $alphaTeam->id);

    expect($member->fresh()->belongsToTeam($zuluTeam))->toBeFalse();
    expect($member->fresh()->current_team_id)->toEqual($alphaTeam->id);
});

test('personal teams cannot be left', function () {
    $user = User::factory()->create();
    $personalTeam = $user->personalTeam();

    $response = $this
        ->actingAs($user)
        ->deleteJson(route('teams.leave', $personalTeam));

    $response->assertForbidden();

    expect($user->fresh()->belongsToTeam($personalTeam))->toBeTrue();
});

test('team owners cannot leave their team', function () {
    $owner = User::factory()->create();
    $team = Team::factory()->create();

    $team->members()->attach($owner, ['role' => TeamRole::Owner->value]);

    $response = $this
        ->actingAs($owner)
        ->deleteJson(route('teams.leave', $team));

    $response->assertForbidden();

    expect($owner->fresh()->belongsToTeam($team))->toBeTrue();
});

test('users cannot leave teams they dont belong to', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create();

    $response = $this
        ->actingAs($user)
        ->deleteJson(route('teams.leave', $team));

    $response->assertForbidden();
});

test('deleting team switches other affected users to their personal team', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();

    $team = Team::factory()->create();
    $team->members()->attach($owner, ['role' => TeamRole::Owner->value]);
    $team->members()->attach($member, ['role' => TeamRole::Member->value]);

    $owner->update(['current_team_id' => $team->id]);
    $member->update(['current_team_id' => $team->id]);

    $response = $this
        ->actingAs($owner)
        ->deleteJson(route('teams.destroy', $team), [
            'name' => $team->name,
        ]);

    $response->assertOk();

    expect($member->fresh()->current_team_id)->toEqual($member->personalTeam()->id);
});

test('personal teams cannot be deleted', function () {
    $user = User::factory()->create();

    $personalTeam = $user->personalTeam();

    $response = $this
        ->actingAs($user)
        ->deleteJson(route('teams.destroy', $personalTeam), [
            'name' => $personalTeam->name,
        ]);

    $response->assertForbidden();

    $this->assertDatabaseHas('teams', [
        'id' => $personalTeam->id,
        'deleted_at' => null,
    ]);
});

test('teams cannot be deleted by non owners', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $team = Team::factory()->create();

    $team->members()->attach($owner, ['role' => TeamRole::Owner->value]);
    $team->members()->attach($member, ['role' => TeamRole::Member->value]);

    $response = $this
        ->actingAs($member)
        ->deleteJson(route('teams.destroy', $team), [
            'name' => $team->name,
        ]);

    $response->assertForbidden();
});

test('users can switch teams', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create();

    $team->members()->attach($user, ['role' => TeamRole::Member->value]);

    $response = $this
        ->actingAs($user)
        ->postJson(route('teams.switch', $team));

    $response
        ->assertOk()
        ->assertJsonPath('currentTeam.id', $team->id);

    expect($user->fresh()->current_team_id)->toEqual($team->id);
});

test('users cannot switch to team they dont belong to', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create();

    $response = $this
        ->actingAs($user)
        ->postJson(route('teams.switch', $team));

    $response->assertForbidden();
});

test('guests cannot access teams', function () {
    $response = $this->get(route('teams.index'));

    $response->assertRedirect(route('login'));
});
