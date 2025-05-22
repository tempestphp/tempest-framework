<?php

namespace Tempest\Cache;

use Tempest\Cache\Config\CacheConfig;
use Tempest\Cache\Config\FilesystemCacheConfig;
use Tempest\Cache\Config\InMemoryCacheConfig;
use Tempest\Cache\Config\PhpCacheConfig;
use Tempest\Container\Container;
use Tempest\Container\GenericContainer;
use Tempest\Core\Insight;
use Tempest\Core\InsightsProvider;
use Tempest\Support\Str;
use UnitEnum;

use function Tempest\Support\arr;

final class UserCacheInsightsProvider implements InsightsProvider
{
    public string $name = 'User caches';

    public function __construct(
        private readonly Container $container,
    ) {}

    public function getInsights(): array
    {
        if (! ($this->container instanceof GenericContainer)) {
            return [];
        }

        return arr($this->container->getSingletons(CacheConfig::class))
            ->map(fn ($_, string $key) => $this->container->get(Cache::class, $key === CacheConfig::class ? null : Str\after_last($key, '#')))
            ->mapWithKeys(fn (Cache $cache) => yield $this->getCacheName($cache) => $this->getInsight($cache))
            ->toArray();
    }

    /** @var Insight[] */
    private function getInsight(Cache $cache): array
    {
        $type = ($cache instanceof GenericCache)
            ? match (get_class($cache->cacheConfig)) {
                FilesystemCacheConfig::class => new Insight('Filesystem'),
                PhpCacheConfig::class => new Insight('PHP'),
                InMemoryCacheConfig::class => new Insight('In-memory'),
                default => null,
            }
            : null;

        if ($cache->enabled) {
            return [$type, new Insight('ENABLED', Insight::SUCCESS)];
        }

        return [$type, new Insight('DISABLED', Insight::WARNING)];
    }

    private function getCacheName(Cache $cache): string
    {
        if (! ($cache instanceof GenericCache)) {
            return $cache::class;
        }

        $tag = $cache->cacheConfig->tag;

        if ($tag instanceof UnitEnum) {
            return $tag->name;
        }

        return $tag ?? 'default';
    }
}
