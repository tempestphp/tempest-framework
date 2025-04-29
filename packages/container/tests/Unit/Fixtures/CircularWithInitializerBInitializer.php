<?php

declare(strict_types=1);

namespace Tempest\Container\Tests\Unit\Fixtures;

use Tempest\Container\Container;
use Tempest\Container\Initializer;

final readonly class CircularWithInitializerBInitializer implements Initializer
{
    public function initialize(Container $container): CircularWithInitializerB
    {
        /** @phpstan-ignore-next-line */
        return new CircularWithInitializerB($container->get(CircularWithInitializerC::class));
    }
}
