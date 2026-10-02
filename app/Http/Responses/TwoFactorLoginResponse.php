<?php

declare(strict_types=1);

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;
use Laravel\Fortify\Contracts\TwoFactorLoginResponse as TwoFactorLoginResponseContract;

final class TwoFactorLoginResponse implements TwoFactorLoginResponseContract
{
    public function toResponse($request): JsonResponse
    {
        return new JsonResponse([
            'two_factor' => false,
        ]);
    }
}
