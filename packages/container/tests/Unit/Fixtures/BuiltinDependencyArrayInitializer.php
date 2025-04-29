<?php

declare(strict_types=1);

namespace Tempest\Container\Tests\Unit\Fixtures;

use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Container\Singleton;

final class BuiltinDependencyArrayInitializer implements Initializer
{
    #[Singleton(tag: 'builtin-dependency-array')]
    public function initialize(Container $container): array
    {
        return ['hallo', 'array', 42];
    }
}
