<?php

namespace Tempest\Core;

use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Container\Singleton;

use function Tempest\env;

final class ConfigCacheInitializer implements Initializer
{
    #[Singleton]
    public function initialize(Container $container): ConfigCache
    {
        return new ConfigCache(
            enabled: $this->shouldCacheBeEnabled(
                $container->get(AppConfig::class)->environment->isProduction(),
            ),
        );
    }

    private function shouldCacheBeEnabled(bool $isProduction): bool
    {
        if (env('INTERNAL_CACHES') === false) {
            return false;
        }

        return (bool) env('CONFIG_CACHE', default: $isProduction);
    }
}
