<?php

declare(strict_types=1);

namespace Tempest\Container\Tests\Fixtures;

use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Container\Lifetime;
use Tempest\Container\Singleton;

final class RequestLifetimeInitializer implements Initializer
{
    #[Singleton(dynamicTags: true, lifetime: Lifetime::REQUEST)]
    public function initialize(Container $container): RequestLifetimeInterface
    {
        return new RequestLifetimeSingleton();
    }
}
