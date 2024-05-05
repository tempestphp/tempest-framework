<?php

declare(strict_types=1);

namespace Tempest\Clock;

use Tempest\Container\Container;
use Tempest\Container\Initializer;

final readonly class TimeboxInitializer implements Initializer
{
    public function initialize(Container $container): Timebox
    {
        return new GenericTimebox($container->get(Clock::class));
    }
}
