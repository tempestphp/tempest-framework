<?php

declare(strict_types=1);

namespace Tests\Tempest\Unit\Container\Fixtures;

use Tempest\Container\Container;
use Tempest\Container\Initializer;

final readonly class CircularWithInitializerBInitializer implements Initializer
{
    public function initialize(Container $container): CircularWithInitializerB
    {
        return new CircularWithInitializerB($container->get(CircularWithInitializerC::class));
    }
}
