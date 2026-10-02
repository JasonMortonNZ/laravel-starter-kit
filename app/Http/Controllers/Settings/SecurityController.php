<?php

declare(strict_types=1);

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\PasswordUpdateRequest;
use App\Http\Requests\Settings\TwoFactorAuthenticationRequest;
use App\Models\User;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rules\Password;
use Laravel\Fortify\Features;

final class SecurityController extends Controller
{
    /**
     * Return the user's security settings.
     */
    public function show(TwoFactorAuthenticationRequest $request, #[CurrentUser] User $user): JsonResponse
    {
        $data = [
            'can_manage_two_factor' => Features::canManageTwoFactorAuthentication(),
            'password_rules' => Password::defaults()->toPasswordRulesString(),
        ];

        if (Features::canManageTwoFactorAuthentication()) {
            $request->ensureStateIsValid();

            $data['two_factor_enabled'] = $user->hasEnabledTwoFactorAuthentication();
            $data['requires_confirmation'] = Features::optionEnabled(Features::twoFactorAuthentication(), 'confirm');
        }

        return response()->json($data);
    }

    /**
     * Update the user's password.
     */
    public function update(PasswordUpdateRequest $request, #[CurrentUser] User $user): JsonResponse
    {
        $user->update([
            'password' => $request->password,
        ]);

        return $this->toast(__('Password updated.'));
    }
}
