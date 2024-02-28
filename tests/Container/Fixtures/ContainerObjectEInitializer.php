<?php

declare(strict_types=1);

namespace Tests\Tempest\Container\Fixtures;

use Tempest\Container\CanInitialize;
use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tests\Tempest\Container\Fixtures;

class ContainerObjectEInitializer implements Initializer, CanInitialize
{
    public function initialize(Container $container): Fixtures\ContainerObjectE
    {
        return new Fixtures\ContainerObjectE();
    }

    public function canInitialize(string $className): bool
    {
        return $className === Fixtures\ContainerObjectE::class;
    }
}
