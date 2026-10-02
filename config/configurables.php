<?php

declare(strict_types=1);

use App\Configurables\FakeSleep;
use App\Configurables\ForceHttps;
use App\Configurables\ImmutableDates;
use App\Configurables\PasswordDefaults;
use App\Configurables\PreventDestructiveCommands;
use App\Configurables\PreventStrayRequests;
use App\Configurables\StrictModels;
use App\Configurables\UnguardModels;

/*
|--------------------------------------------------------------------------
| Configurables
|--------------------------------------------------------------------------
|
| Every Configurable class must be listed here to run. The value is its
| enabled flag, so set a class to false to switch it off.
|
*/

return [
    FakeSleep::class => true,
    ForceHttps::class => true,
    StrictModels::class => true,
    UnguardModels::class => true,
    ImmutableDates::class => true,
    PasswordDefaults::class => true,
    PreventStrayRequests::class => true,
    PreventDestructiveCommands::class => true,
];
