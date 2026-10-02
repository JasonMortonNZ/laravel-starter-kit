<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Resources\PendingInvitationResource;
use App\Models\TeamInvitation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class DashboardController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user();

        $pendingInvitations = TeamInvitation::query()
            ->with(['inviter', 'team'])
            ->pending()
            ->whereRaw('LOWER(email) = ?', [mb_strtolower($user->email)])
            ->latest()
            ->get();

        return response()->json([
            'currentTeam' => $user->toCurrentUserTeam(),
            'pendingInvitations' => PendingInvitationResource::collection($pendingInvitations),
        ]);
    }
}
