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
            enabled: $this->shouldCacheBeEnabled(),
        );
    }

    private function shouldCacheBeEnabled(): bool
    {
        if (env('INTERNAL_CACHES') === false) {
            return false;
        }

        return (bool) env('CONFIG_CACHE', default: Environment::guessFromEnvironment()->requiresCaution());
    }
}
