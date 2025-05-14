<?php

namespace Tempest\View;

use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Container\Singleton;

use function Tempest\env;

final class ViewCacheInitializer implements Initializer
{
    #[Singleton]
    public function initialize(Container $container): ViewCache
    {
        return new ViewCache(
            enabled: $this->shouldCacheBeEnabled(),
        );
    }

    private function shouldCacheBeEnabled(): bool
    {
        if (env('CACHE') === true) {
            return true;
        }

        return (bool) env('VIEW_CACHE', default: false);
    }
}
