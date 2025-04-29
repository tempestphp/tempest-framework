<?php

declare(strict_types=1);

namespace Tempest\Container\Tests\Unit\Fixtures;

use Tempest\Container\Container;
use Tempest\Container\Initializer;

final class IntersectionInitializer implements Initializer
{
    public function initialize(Container $container): UnionInterfaceA&UnionInterfaceB
    {
        return new UnionImplementation();
    }
}
