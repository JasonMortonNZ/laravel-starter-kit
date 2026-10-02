<?php

declare(strict_types=1);

namespace App\Http\Controllers\Teams;

use App\Enums\TeamRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Teams\UpdateTeamMemberRequest;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

final class TeamMemberController extends Controller
{
    /**
     * Update the specified team member's role.
     */
    public function update(UpdateTeamMemberRequest $request, Team $team, User $user): JsonResponse
    {
        Gate::authorize('updateMember', $team);

        $newRole = $request->enum('role', TeamRole::class);

        $team->memberships()
            ->where('user_id', $user->id)
            ->firstOrFail()
            ->update(['role' => $newRole]);

        return $this->toast(__('Member role updated.'));
    }

    /**
     * Remove the specified team member.
     */
    public function destroy(Team $team, User $user): JsonResponse
    {
        Gate::authorize('removeMember', $team);

        abort_if($team->owner()?->is($user) === true, 403, __('The team owner cannot be removed.'));

        $team->memberships()
            ->where('user_id', $user->id)
            ->delete();

        $personalTeam = $user->personalTeam();

        if ($user->isCurrentTeam($team) && $personalTeam instanceof Team) {
            $user->switchTeam($personalTeam);
        }

        return $this->toast(__('Member removed.'));
    }
}
