<?php

declare(strict_types=1);

namespace App\Providers;

use App\Contracts\Configurable;
use Illuminate\Support\ServiceProvider;

final class ConfigurableServiceProvider extends ServiceProvider
{
    /**
     * Run every Configurable registered in config/configurables.php.
     */
    public function boot(): void
    {
        collect(config()->array('configurables'))
            ->keys()
            ->map(fn (int|string $configurable): mixed => $this->app->make((string) $configurable))
            ->ensure(Configurable::class)
            ->filter(fn (Configurable $configurable): bool => $configurable->enabled())
            ->each(fn (Configurable $configurable) => $configurable->configure());
    }
}
