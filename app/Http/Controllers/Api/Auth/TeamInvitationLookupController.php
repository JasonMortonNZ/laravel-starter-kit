<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\TeamInvitation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class TeamInvitationLookupController extends Controller
{
    /**
     * Resolve a pending team invitation code into context for the auth pages.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $code = $request->query('code');

        abort_unless(is_string($code), 404);

        $invitation = TeamInvitation::query()
            ->with('team')
            ->pending()
            ->where('code', $code)
            ->firstOrFail();

        return response()->json([
            'code' => $invitation->code,
            'team_name' => $invitation->team->name,
        ]);
    }
}
