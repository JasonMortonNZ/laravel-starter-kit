<?php

declare(strict_types=1);

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\PasswordUpdateRequest;
use App\Http\Requests\Settings\TwoFactorAuthenticationRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rules\Password;
use Laravel\Fortify\Features;

final class SecurityController extends Controller
{
    /**
     * Return the user's security settings.
     */
    public function show(TwoFactorAuthenticationRequest $request): JsonResponse
    {
        $data = [
            'canManageTwoFactor' => Features::canManageTwoFactorAuthentication(),
            'passwordRules' => Password::defaults()->toPasswordRulesString(),
        ];

        if (Features::canManageTwoFactorAuthentication()) {
            $request->ensureStateIsValid();

            $data['twoFactorEnabled'] = $request->user()->hasEnabledTwoFactorAuthentication();
            $data['requiresConfirmation'] = Features::optionEnabled(Features::twoFactorAuthentication(), 'confirm');
        }

        return response()->json($data);
    }

    /**
     * Update the user's password.
     */
    public function update(PasswordUpdateRequest $request): JsonResponse
    {
        $request->user()->update([
            'password' => $request->password,
        ]);

        return $this->toast(__('Password updated.'));
    }
}
