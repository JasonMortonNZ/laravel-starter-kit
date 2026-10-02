<?php

declare(strict_types=1);

use App\Enums\TeamRole;
use App\Http\Middleware\EnsureTeamMembership;
use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\Route;

beforeEach(function (): void {
    Route::middleware(['web', 'auth', EnsureTeamMembership::class.':admin'])
        ->get('/api/_test/teams/{team}/admin', fn (): string => 'ok');

    Route::middleware(['web', 'auth', EnsureTeamMembership::class.':superuser'])
        ->get('/api/_test/teams/{team}/unknown-role', fn (): string => 'ok');
});

test('members with at least the required role can access the route', function (TeamRole $role): void {
    $user = User::factory()->create();
    $team = Team::factory()->create();

    $team->members()->attach($user, ['role' => $role->value]);

    $this
        ->actingAs($user)
        ->get("/api/_test/teams/{$team->slug}/admin")
        ->assertOk();
})->with([
    'owner' => [TeamRole::Owner],
    'admin' => [TeamRole::Admin],
]);

test('members below the required role are forbidden', function (): void {
    $user = User::factory()->create();
    $team = Team::factory()->create();

    $team->members()->attach($user, ['role' => TeamRole::Member->value]);

    $this
        ->actingAs($user)
        ->get("/api/_test/teams/{$team->slug}/admin")
        ->assertForbidden();
});

test('an unknown required role forbids everyone', function (): void {
    $user = User::factory()->create();
    $team = Team::factory()->create();

    $team->members()->attach($user, ['role' => TeamRole::Owner->value]);

    $this
        ->actingAs($user)
        ->get("/api/_test/teams/{$team->slug}/unknown-role")
        ->assertForbidden();
});

test('visiting a team scoped page switches the current team', function (): void {
    $user = User::factory()->create();
    $team = Team::factory()->create();

    $team->members()->attach($user, ['role' => TeamRole::Member->value]);

    $this
        ->actingAs($user)
        ->get(route('dashboard', ['current_team' => $team->slug]))
        ->assertOk();

    expect($user->fresh()->current_team_id)->toBe($team->id);
});
