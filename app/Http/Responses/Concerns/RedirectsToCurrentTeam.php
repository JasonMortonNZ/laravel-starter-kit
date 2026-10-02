<?php

declare(strict_types=1);

namespace App\Http\Responses\Concerns;

use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

trait RedirectsToCurrentTeam
{
    /**
     * Get the path the SPA should navigate to after authenticating: the intended
     * URL if one was stored by a guarded deep link, otherwise the team-scoped default.
     */
    protected function intendedPath(Request $request, string $redirect): string
    {
        $intended = $request->session()->pull('url.intended');

        return is_string($intended) && $intended !== ''
            ? $intended
            : $this->redirectPathForCurrentTeam($request, $redirect);
    }

    protected function redirectPathForCurrentTeam(Request $request, string $redirect): string
    {
        $team = $this->currentTeam($request);

        URL::defaults(['current_team' => $team->slug]);

        return "/{$team->slug}{$redirect}";
    }

    protected function currentTeam(Request $request): Team
    {
        $user = $request->user();

        abort_if(! $user, 403);

        $team = $user->currentTeam ?? $user->personalTeam();

        abort_if(! $team, 403);

        return $team;
    }
}
