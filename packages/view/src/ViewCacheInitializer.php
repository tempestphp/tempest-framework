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
        $viewCache = new ViewCache(
            enabled: $this->shouldCacheBeEnabled(
                $container->get(AppConfig::class)->environment->isProduction(),
            ),
        );

        if (PHP_OS_FAMILY === 'Windows') {
            dump($viewCache, env('VIEW_CACHE'), env('INTERNAL_CACHES'));
        }

        return $viewCache;
    }

    private function shouldCacheBeEnabled(bool $isProduction): bool
    {
        if (env('INTERNAL_CACHES') === false) {
            return false;
        }

        return (bool) env('VIEW_CACHE', default: $isProduction);
    }
}
