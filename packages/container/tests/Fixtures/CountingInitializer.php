<?php

declare(strict_types=1);

namespace Tempest\Container\Tests\Fixtures;

use Tempest\Container\Container;
use Tempest\Container\Initializer;

final class CountingInitializer implements Initializer
{
    public static int $instances = 0;

    public function __construct()
    {
        self::$instances++;
    }

    public function initialize(Container $container): ContainerObjectD
    {
        return new ContainerObjectD(prop: 'counted');
    }
}
