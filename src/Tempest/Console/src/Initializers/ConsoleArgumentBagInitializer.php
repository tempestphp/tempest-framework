<?php

declare(strict_types=1);

namespace Tempest\Console\Initializers;

use Tempest\Console\Input\ConsoleArgumentBag;
use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Container\Singleton;

final readonly class ConsoleArgumentBagInitializer implements Initializer
{
    #[Singleton]
    public function initialize(Container $container): ConsoleArgumentBag
    {
        return new ConsoleArgumentBag($_SERVER['argv']);
    }
}
