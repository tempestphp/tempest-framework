<?php

namespace Tempest\Cryptography;

use Tempest\Clock\Clock;
use Tempest\Container\Container;
use Tempest\Container\Initializer;

final class TimelockInitializer implements Initializer
{
    public function initialize(Container $container): Timelock
    {
        return new Timelock($container->get(Clock::class));
    }
}
