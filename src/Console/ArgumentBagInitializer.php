<?php

declare(strict_types=1);

namespace Tempest\Console;

use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Container\Singleton;

#[Singleton]
final class ArgumentBagInitializer implements Initializer
{
    public function initialize(Container $container): ArgumentBag
    {
        $arguments = $_SERVER['argv'];

        return new GenericArgumentBag($arguments);
    }
}
