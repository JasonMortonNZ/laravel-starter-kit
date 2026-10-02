<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

abstract class Controller
{
    /**
     * Create a JSON response carrying a toast notification.
     *
     * @param  array<string, mixed>  $data
     */
    protected function toast(string $message, array $data = [], int $status = 200, string $type = 'success'): JsonResponse
    {
        return response()->json([
            'toast' => ['type' => $type, 'message' => $message],
            ...$data,
        ], $status);
    }
}
