<?php

namespace Tempest\Icon;

use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Container\Singleton;
use Tempest\EventBus\EventBus;
use Tempest\HttpClient\HttpClient;
use Tempest\Icon\IconCache;

final class IconInitializer implements Initializer
{
    #[Singleton]
    public function initialize(Container $container): Icon
    {
        return new Icon(
            iconCache: $container->get(IconCache::class),
            iconConfig: $container->get(IconConfig::class),
            http: $container->get(HttpClient::class),
            eventBus: interface_exists(EventBus::class)
                ? $container->get(EventBus::class)
                : null,
        );
    }
}
