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
            strategy: $this->resolveDiscoveryCacheStrategy(
                $container->get(AppConfig::class)->environment->isProduction(),
            ),
        );
    }

    private function resolveDiscoveryCacheStrategy(bool $isProduction): DiscoveryCacheStrategy
    {
        if ($this->isDiscoveryGenerateCommand() || $this->isDiscoveryClearCommand()) {
            return DiscoveryCacheStrategy::NONE;
        }

        $current = DiscoveryCacheStrategy::make(env('DISCOVERY_CACHE', default: $isProduction));

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

        return $command === 'dg' || $command === 'discovery:generate' || $command === 'd:g';
    }

    private function isDiscoveryClearCommand(): bool
    {
        if (PHP_SAPI !== 'cli') {
            return false;
        }

        $command = $_SERVER['argv'][1] ?? null;

        return $command === 'dc' || $command === 'discovery:clear' || $command === 'd:c';
    }
}
