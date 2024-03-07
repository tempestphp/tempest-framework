<?php

declare(strict_types=1);

namespace Tempest\Http\Session;

use Tempest\Clock\Clock;
use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Container\Singleton;
use Tempest\Http\Session\Managers\FileSessionManager;

#[Singleton]
final readonly class SessionManagerInitializer implements Initializer
{
    public function initialize(Container $container): SessionManager
    {
        // TODO: make configurable
        return new FileSessionManager(
            $container->get(Clock::class),
            $container->get(SessionConfig::class),
        );
    }
}
