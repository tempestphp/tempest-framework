<?php

declare(strict_types=1);

namespace Tempest\Http\Session;

use Tempest\AppConfig;
use Tempest\Clock\GenericClock;
use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Container\Singleton;

#[Singleton]
final readonly class SessionInitializer implements Initializer
{
    public function initialize(Container $container): Session
    {
        $session = new Session(new FileSessionHandler(
            clock: new GenericClock(),
            appConfig: $container->get(AppConfig::class),
        ));

        $session->start();

        return $session;
    }
}
