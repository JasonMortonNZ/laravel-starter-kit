<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;
use Laravel\Fortify\Features;

final class BootstrapController extends Controller
{
    /**
     * Return the shared application state the SPA needs on load.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'name' => config('app.name'),
            'auth' => [
                'user' => $user ? UserResource::make($user) : null,
            ],
            'currentTeam' => $user?->toCurrentUserTeam(),
            'teams' => $user?->toUserTeams(includeCurrent: true) ?? [],
            'features' => [
                'canRegister' => Features::enabled(Features::registration()),
                'canResetPassword' => Features::enabled(Features::resetPasswords()),
                'canManageTwoFactor' => Features::canManageTwoFactorAuthentication(),
                'requiresTwoFactorConfirmation' => Features::optionEnabled(Features::twoFactorAuthentication(), 'confirm'),
                'mustVerifyEmail' => $user instanceof MustVerifyEmail,
            ],
            'passwordRules' => Password::defaults()->toPasswordRulesString(),
        ]);
    }
}
