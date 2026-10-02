<?php

declare(strict_types=1);

use App\Http\Controllers\Api\Auth\TeamInvitationLookupController;
use App\Http\Controllers\Api\BootstrapController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Settings\SecurityController;
use App\Http\Controllers\Teams\TeamController;
use App\Http\Middleware\EnsureTeamMembership;
use Illuminate\Auth\Middleware\RequirePassword;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| JSON API consumed by the Vue SPA
|--------------------------------------------------------------------------
|
| These routes are loaded inside the "web" middleware group so the session
| cookie and CSRF protection apply exactly as they do for Fortify's routes.
|
*/

Route::get('bootstrap', BootstrapController::class)->name('bootstrap');
Route::get('auth/invitation', TeamInvitationLookupController::class)->name('auth.invitation');

Route::prefix('{current_team}')
    ->middleware(['auth', 'verified', EnsureTeamMembership::class])
    ->group(function (): void {
        Route::get('dashboard', DashboardController::class)->name('dashboard');
    });

Route::middleware(['auth', 'verified'])->group(function (): void {
    Route::get('settings/security', [SecurityController::class, 'show'])
        ->middleware(RequirePassword::class)
        ->name('settings.security');

    Route::get('settings/teams', [TeamController::class, 'index'])->name('teams.index');

    Route::get('settings/teams/{team}', [TeamController::class, 'show'])
        ->middleware(EnsureTeamMembership::class)
        ->name('teams.show');
});
