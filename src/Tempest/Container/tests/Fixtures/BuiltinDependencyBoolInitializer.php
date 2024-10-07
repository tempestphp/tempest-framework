<?php

declare(strict_types=1);

namespace Tempest\Container\Tests\Fixtures;

use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Container\Singleton;

final class BuiltinDependencyBoolInitializer implements Initializer
{
    #[Singleton(tag:"builtin-dependency-bool")]
    public function initialize(Container $container): bool
    {
        return true;
    }
}
