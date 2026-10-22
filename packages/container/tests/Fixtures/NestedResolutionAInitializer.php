<?php

declare(strict_types=1);

namespace Tempest\Container\Tests\Fixtures;

use Tempest\Container\Container;
use Tempest\Container\Initializer;

final readonly class NestedResolutionAInitializer implements Initializer
{
    public function initialize(Container $container): NestedResolutionA
    {
        // This nested resolution used to reset the chain, hiding the cycle below.
        $container->get(Unrelated::class);
        $container->get(NestedResolutionB::class);

        return new NestedResolutionA();
    }
}
