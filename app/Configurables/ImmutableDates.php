<?php

declare(strict_types=1);

namespace App\Configurables;

use App\Contracts\Configurable;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;

final readonly class ImmutableDates implements Configurable
{
    /**
     * Whether the configurable is enabled or not.
     */
    public function enabled(): bool
    {
        return config()->boolean(sprintf('configurables.%s', self::class), true);
    }

    /**
     * Run the configurable.
     */
    public function configure(): void
    {
        Date::use(CarbonImmutable::class);
    }
}
