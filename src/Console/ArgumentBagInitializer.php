<?php

declare(strict_types=1);

namespace Tempest\Console;

use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Container\Singleton;

#[Singleton]
final readonly class ArgumentBagInitializer implements Initializer
{
    public function initialize(Container $container): ArgumentBag
    {
        $arguments = $_SERVER['argv'];

        return match (! ! $arguments) {
            true => new GenericArgumentBag($arguments),
            false => new NullArgumentBag(),
        };
    }
}
