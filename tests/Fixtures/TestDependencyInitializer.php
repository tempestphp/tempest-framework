<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures;

use Tempest\Container\Container;
use Tempest\Container\Initializer;

final readonly class TestDependencyInitializer implements Initializer
{
    public function initialize(Container $container): TestDependency
    {
        return new TestDependency('test');
    }
}
