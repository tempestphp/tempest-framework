<?php

declare(strict_types=1);

namespace Tempest\Http\Session;

use Tempest\AppConfig;
use Tempest\Clock\Clock;
use Tempest\Clock\GenericClock;
use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Container\Singleton;
use Tempest\Http\Request;
use Tempest\Http\Session\Drivers\FileSessionDriver;
use Tempest\Http\Session\Resolvers\HeaderSessionResolver;

#[Singleton]
final readonly class SessionInitializer implements Initializer
{
    public function initialize(Container $container): Session
    {
        // TODO: support configurable resolvers
        $id = (new HeaderSessionResolver($container->get(Request::class)))->resolve();

        // TODO: make session class configurable
        $session = new FileSession(
            clock: $container->get(Clock::class),
            appConfig: $container->get(AppConfig::class),
            id: $id,
        );

        if (! $session->isValid()) {
            $session->create();
        }

        return $session;
    }
}
