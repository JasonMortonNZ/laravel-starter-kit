<?php

declare(strict_types=1);

use App\Http\Controllers\Teams\TeamInvitationController;
use App\Http\Middleware\EnsureTeamMembership;
use App\Http\Middleware\ForceJsonAccept;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Fortify;

/*
|--------------------------------------------------------------------------
| SPA shell routes
|--------------------------------------------------------------------------
|
| Every page is rendered client-side by the Vue application, so these routes
| only serve the application shell. They keep the route names Laravel and
| Fortify rely on (guest redirects, password reset emails, verification and
| password confirmation prompts) and apply the same middleware as the API so
| deep links are guarded server-side as well.
|
*/

Route::view('/', 'app')->name('home');

Route::middleware('guest')->group(function (): void {
    Route::view('login', 'app')->name('login');
    Route::view('register', 'app')->name('register');
    Route::view('forgot-password', 'app')->name('password.request');
    Route::view('reset-password/{token}', 'app')->name('password.reset');

    Route::get('two-factor-challenge', fn (Request $request): View|RedirectResponse => $request->session()->has('login.id')
        ? view('app')
        : to_route('login'))->name('two-factor.login');
});

Route::middleware('auth')->group(function (): void {
    Route::get('email/verify', fn (Request $request): View|RedirectResponse => $request->user()?->hasVerifiedEmail()
        ? redirect()->intended(Fortify::redirects('email-verification'))
        : view('app'))->name('verification.notice');

    Route::view('user/confirm-password', 'app')->name('password.confirm');

    Route::post('invitations/{invitation}/accept', [TeamInvitationController::class, 'accept'])->name('invitations.accept');
    Route::delete('invitations/{invitation}', [TeamInvitationController::class, 'decline'])->name('invitations.decline');
});

Route::prefix('{current_team}')
    ->middleware(['auth', 'verified', EnsureTeamMembership::class])
    ->group(function (): void {
        Route::view('dashboard', 'app')->name('dashboard');
    });

require __DIR__.'/settings.php';

Route::prefix('api')
    ->name('api.')
    ->middleware(ForceJsonAccept::class)
    ->group(base_path('routes/api.php'));

Route::view('/{any?}', 'app')
    ->where('any', '^(?!api/|storage/|up$).*$')
    ->name('spa');
