<?php

namespace Tempest\View;

use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Container\Singleton;
use Tempest\Core\AppConfig;

use function Tempest\env;

final class ViewCacheInitializer implements Initializer
{
    #[Singleton]
    public function initialize(Container $container): ViewCache
    {
        return new ViewCache(
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

        return (bool) env('VIEW_CACHE', default: $isProduction);
    }
}
