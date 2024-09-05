<?php

declare(strict_types=1);

namespace Tempest\Core;

use NunoMaduro\Collision\Provider;
use Spatie\Ignition\Ignition;

final readonly class GenericExceptionHandlerSetup implements ExceptionHandlerSetup
{
    public function initialize(): void
    {
        if ($_SERVER['argv'] ?? null) {
            (new Provider())->register();
        } else {
            Ignition::make()->register();
        }
    }
}
