<?php

namespace Tempest\View;

use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Container\Singleton;

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
        if (env('CACHE') === true) {
            return true;
        }

        return (bool) env('ICON_CACHE', default: true);
    }
}
