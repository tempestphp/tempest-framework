<?php

declare(strict_types=1);

namespace Tempest\Container\Tests\Fixtures;

use Tempest\Container\Container;
use Tempest\Container\Initializer;

final readonly class NestedResolutionBInitializer implements Initializer
{
    public function initialize(Container $container): NestedResolutionB
    {
        $container->get(NestedResolutionA::class);

        return new NestedResolutionB();
    }
}
