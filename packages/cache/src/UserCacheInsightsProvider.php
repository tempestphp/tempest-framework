<?php

namespace Tempest\Cache;

use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\Adapter\PhpFilesAdapter;
use Symfony\Component\Cache\Adapter\RedisAdapter;
use Tempest\Cache\Config\CacheConfig;
use Tempest\Cache\Config\InMemoryCacheConfig;
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
            ? match (get_class($cache->adapter)) {
                FilesystemAdapter::class => new Insight('Filesystem'),
                PhpFilesAdapter::class => new Insight('PHP'),
                ArrayAdapter::class => new Insight('In-memory'),
                RedisAdapter::class => new Insight('Redis'),
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

        if ($cache->tag instanceof UnitEnum) {
            return $cache->tag->name;
        }

        return $cache->tag ?? 'default';
    }
}
