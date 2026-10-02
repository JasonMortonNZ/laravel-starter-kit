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
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

final class TeamController extends Controller
{
    /**
     * List the user's teams.
     */
    public function index(Request $request): JsonResponse
    {
        return response()->json([
            'teams' => $request->user()->toUserTeams(includeCurrent: true),
        ]);
    }

    /**
     * Store a newly created team.
     */
    public function store(SaveTeamRequest $request, CreateTeam $createTeam): JsonResponse
    {
        $team = $createTeam->handle($request->user(), $request->validated('name'));

        return $this->toast(__('Team created.'), [
            'team' => TeamResource::make($team),
            'redirect' => route('teams.edit', ['team' => $team->slug], false),
        ], 201);
    }

    /**
     * Return the team with its members, invitations, and the user's permissions.
     */
    public function show(Request $request, Team $team): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'team' => TeamResource::make($team),
            'members' => TeamMemberResource::collection($team->members()->get()),
            'invitations' => TeamInvitationResource::collection($team->invitations()->whereNull('accepted_at')->get()),
            'permissions' => $user->toTeamPermissions($team),
            'availableRoles' => TeamRole::assignable(),
        ]);
    }

    /**
     * Update the specified team.
     */
    public function update(SaveTeamRequest $request, Team $team): JsonResponse
    {
        Gate::authorize('update', $team);

        $team = DB::transaction(function () use ($request, $team) {
            $team = Team::whereKey($team->id)->lockForUpdate()->firstOrFail();

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
    public function switch(Request $request, Team $team): JsonResponse
    {
        abort_unless($request->user()->belongsToTeam($team), 403);

        $request->user()->switchTeam($team);

        return response()->json([
            'currentTeam' => $request->user()->toCurrentUserTeam(),
        ]);
    }

    /**
     * Leave the specified team.
     */
    public function leave(Request $request, Team $team): JsonResponse
    {
        Gate::authorize('leave', $team);

        $user = $request->user();

        $fallbackTeam = $user->isCurrentTeam($team)
            ? $user->fallbackTeam($team)
            : null;

        $team->memberships()
            ->where('user_id', $user->id)
            ->delete();

        if ($fallbackTeam) {
            $user->switchTeam($fallbackTeam);
        }

        return $this->toast(__('You left the team ":name"', ['name' => $team->name]), [
            'redirect' => route('teams.index', absolute: false),
            'currentTeam' => $user->toCurrentUserTeam(),
        ]);
    }

    /**
     * Delete the specified team.
     */
    public function destroy(DeleteTeamRequest $request, Team $team): JsonResponse
    {
        $user = $request->user();
        $fallbackTeam = $user->isCurrentTeam($team)
            ? $user->fallbackTeam($team)
            : null;

        DB::transaction(function () use ($user, $team) {
            User::where('current_team_id', $team->id)
                ->where('id', '!=', $user->id)
                ->each(fn (User $affectedUser) => $affectedUser->switchTeam($affectedUser->personalTeam()));

            $team->invitations()->delete();
            $team->memberships()->delete();
            $team->delete();
        });

        if ($fallbackTeam) {
            $user->switchTeam($fallbackTeam);
        }

        return $this->toast(__('Team deleted.'), [
            'redirect' => route('teams.index', absolute: false),
            'currentTeam' => $user->toCurrentUserTeam(),
        ]);
    }
}
