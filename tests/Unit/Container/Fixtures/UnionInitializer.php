<?php

declare(strict_types=1);

namespace Tests\Tempest\Unit\Container\Fixtures;

use Tempest\Container\Container;
use Tempest\Container\Initializer;

class UnionInitializer implements Initializer
{
    public function initialize(Container $container): UnionInterfaceA|UnionInterfaceB
    {
        return new UnionImplementation();
    }
}
