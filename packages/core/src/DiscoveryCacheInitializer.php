<?php

namespace Tempest\Core;

use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Container\Singleton;

use function Tempest\env;

final class DiscoveryCacheInitializer implements Initializer
{
    #[Singleton]
    public function initialize(Container $container): DiscoveryCache
    {
        return new DiscoveryCache(
            strategy: $this->resolveDiscoveryCacheStrategy(),
        );
    }

    private function resolveDiscoveryCacheStrategy(): DiscoveryCacheStrategy
    {
        if ($this->isDiscoveryGenerateCommand()) {
            return DiscoveryCacheStrategy::NONE;
        }

        $cache = env('CACHE');

        if ($cache !== null) {
            $current = DiscoveryCacheStrategy::make($cache);
        } else {
            $current = DiscoveryCacheStrategy::make(env('DISCOVERY_CACHE'));
        }

        if ($current === DiscoveryCacheStrategy::NONE) {
            return $current;
        }

        $original = DiscoveryCacheStrategy::make(@file_get_contents(DiscoveryCache::getCurrentDiscoverStrategyCachePath()));

        if ($current !== $original) {
            return DiscoveryCacheStrategy::INVALID;
        }

        return $current;
    }

    private function isDiscoveryGenerateCommand(): bool
    {
        if (PHP_SAPI !== 'cli') {
            return false;
        }

        $command = $_SERVER['argv'][1] ?? null;

        return $command === 'dg' || $command === 'discovery:generate';
    }
}
