<?php

declare(strict_types=1);

namespace App\Http\Controllers\Teams;

use App\Actions\Teams\CreateTeam;
use App\Enums\TeamRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Teams\DeleteTeamRequest;
use App\Http\Requests\Teams\SaveTeamRequest;
use App\Http\Resources\TeamInvitationResource;
use App\Http\Resources\TeamMemberResource;
use App\Http\Resources\TeamResource;
use App\Models\Team;
use App\Models\User;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

final class TeamController extends Controller
{
    /**
     * List the user's teams.
     */
    public function index(#[CurrentUser] User $user): JsonResponse
    {
        return response()->json([
            'teams' => $user->toUserTeams(includeCurrent: true),
        ]);
    }

    /**
     * Store a newly created team.
     */
    public function store(SaveTeamRequest $request, CreateTeam $createTeam, #[CurrentUser] User $user): JsonResponse
    {
        $team = $createTeam->handle($user, $request->string('name')->toString());

        return $this->toast(__('Team created.'), [
            'team' => TeamResource::make($team),
        ], 201);
    }

    /**
     * Return the team with its members, invitations, and the user's permissions.
     */
    public function show(Team $team, #[CurrentUser] User $user): JsonResponse
    {
        return response()->json([
            'team' => TeamResource::make($team),
            'members' => TeamMemberResource::collection($team->members()->get()),
            'invitations' => TeamInvitationResource::collection($team->invitations()->whereNull('accepted_at')->get()),
            'permissions' => $user->toTeamPermissions($team),
            'available_roles' => TeamRole::assignable(),
        ]);
    }

    /**
     * Update the specified team.
     */
    public function update(SaveTeamRequest $request, Team $team): JsonResponse
    {
        Gate::authorize('update', $team);

        $team = DB::transaction(function () use ($request, $team) {
            $team = Team::query()->whereKey($team->id)->lockForUpdate()->firstOrFail();

            $team->update(['name' => $request->validated('name')]);

            return $team;
        });

        return $this->toast(__('Team updated.'), [
            'team' => TeamResource::make($team),
        ]);
    }

    /**
     * Switch the user's current team.
     */
    public function switch(Team $team, #[CurrentUser] User $user): JsonResponse
    {
        abort_unless($user->belongsToTeam($team), 403);

        $user->switchTeam($team);

        return response()->json([
            'current_team' => $user->toCurrentUserTeam(),
        ]);
    }

    /**
     * Leave the specified team.
     */
    public function leave(Team $team, #[CurrentUser] User $user): JsonResponse
    {
        Gate::authorize('leave', $team);

        $fallbackTeam = $user->isCurrentTeam($team)
            ? $user->fallbackTeam($team)
            : null;

        $team->memberships()
            ->where('user_id', $user->id)
            ->delete();

        if ($fallbackTeam instanceof Team) {
            $user->switchTeam($fallbackTeam);
        }

        return $this->toast(__('You left the team ":name"', ['name' => $team->name]), [
            'current_team' => $user->toCurrentUserTeam(),
        ]);
    }

    /**
     * Delete the specified team.
     */
    public function destroy(DeleteTeamRequest $request, Team $team, #[CurrentUser] User $user): JsonResponse
    {
        $fallbackTeam = $user->isCurrentTeam($team)
            ? $user->fallbackTeam($team)
            : null;

        DB::transaction(function () use ($user, $team): void {
            User::query()->where('current_team_id', $team->id)
                ->where('id', '!=', $user->id)
                ->each(function (User $affectedUser): void {
                    $personalTeam = $affectedUser->personalTeam();

                    if ($personalTeam instanceof Team) {
                        $affectedUser->switchTeam($personalTeam);
                    }
                });

            $team->invitations()->delete();
            $team->memberships()->delete();
            $team->delete();
        });

        if ($fallbackTeam instanceof Team) {
            $user->switchTeam($fallbackTeam);
        }

        return $this->toast(__('Team deleted.'), [
            'current_team' => $user->toCurrentUserTeam(),
        ]);
    }
}
