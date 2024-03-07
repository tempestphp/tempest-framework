<?php

declare(strict_types=1);

namespace Tempest\Clock;

use Tempest\Container\Container;
use Tempest\Container\Initializer;

final readonly class ClockInitializer implements Initializer
{
    public function initialize(Container $container): Clock
    {
        return new GenericClock();
    }
}
