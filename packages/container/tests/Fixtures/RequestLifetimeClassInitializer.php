<?php

declare(strict_types=1);

namespace Tempest\Container\Tests\Fixtures;

use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Container\Lifetime;
use Tempest\Container\Singleton;

#[Singleton(lifetime: Lifetime::REQUEST)]
final class RequestLifetimeClassInitializer implements Initializer
{
    public function initialize(Container $container): RequestLifetimeInterface
    {
        return new RequestLifetimeSingleton();
    }
}
