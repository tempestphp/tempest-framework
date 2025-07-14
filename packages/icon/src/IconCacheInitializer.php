<?php

namespace Tempest\Icon;

use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Container\Singleton;
use Tempest\Core\AppConfig;

use function Tempest\env;

final class IconCacheInitializer implements Initializer
{
    #[Singleton]
    public function initialize(Container $container): IconCache
    {
        return new IconCache(
            enabled: $this->shouldCacheBeEnabled(),
        );
    }

    private function shouldCacheBeEnabled(): bool
    {
        if (env('INTERNAL_CACHES') === false) {
            return false;
        }

        return (bool) env('ICON_CACHE', default: true);
    }
}
