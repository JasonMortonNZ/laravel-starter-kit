<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Resources\PendingInvitationResource;
use App\Models\TeamInvitation;
use App\Models\User;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\JsonResponse;

final class DashboardController extends Controller
{
    public function __invoke(#[CurrentUser] User $user): JsonResponse
    {
        $pendingInvitations = TeamInvitation::query()
            ->with(['inviter', 'team'])
            ->pending()
            ->where('email', $user->email)
            ->latest()
            ->get();

        return response()->json([
            'current_team' => $user->toCurrentUserTeam(),
            'pending_invitations' => PendingInvitationResource::collection($pendingInvitations),
        ]);
    }
}
