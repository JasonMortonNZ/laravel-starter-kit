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
            'current_team' => $user?->toCurrentUserTeam(),
            'teams' => $user?->toUserTeams(includeCurrent: true) ?? [],
            'features' => [
                'can_register' => Features::enabled(Features::registration()),
                'can_reset_password' => Features::enabled(Features::resetPasswords()),
                'can_manage_two_factor' => Features::canManageTwoFactorAuthentication(),
                'requires_two_factor_confirmation' => Features::optionEnabled(Features::twoFactorAuthentication(), 'confirm'),
                'must_verify_email' => $user instanceof MustVerifyEmail,
            ],
            'password_rules' => Password::defaults()->toPasswordRulesString(),
        ]);
    }
}
