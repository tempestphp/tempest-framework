<?php

declare(strict_types=1);

namespace Tempest\Container\Tests\Unit\Fixtures;

use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Container\Singleton;

final class BuiltinDependencyStringInitializer implements Initializer
{
    #[Singleton(tag: 'builtin-dependency-string')]
    public function initialize(Container $container): string
    {
        return 'Hallo dependency!';
    }
}
