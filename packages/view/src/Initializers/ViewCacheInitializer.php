<?php

namespace Tempest\View\Initializers;

use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Container\Singleton;
use Tempest\Core\Environment;
use Tempest\View\ViewCache;

use function Tempest\env;

final class ViewCacheInitializer implements Initializer
{
    #[Singleton]
    public function initialize(Container $container): ViewCache
    {
        $viewCache = new ViewCache(
            enabled: $this->shouldCacheBeEnabled(),
        );

        return $viewCache;
    }

    private function shouldCacheBeEnabled(): bool
    {
        if (env('INTERNAL_CACHES') === false) {
            return false;
        }

        return (bool) env('VIEW_CACHE', default: Environment::guessFromEnvironment());
    }
}
