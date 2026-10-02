<?php

declare(strict_types=1);

namespace App\Configurables;

use App\Contracts\Configurable;
use Illuminate\Database\Eloquent\Model;

final readonly class StrictModels implements Configurable
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
        Model::shouldBeStrict();
    }
}
