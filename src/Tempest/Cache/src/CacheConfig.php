<?php

declare(strict_types=1);

namespace Tempest\Cache;

use Psr\Cache\CacheItemPoolInterface;
use function Tempest\env;

final class CacheConfig
{
    /** @var class-string<\Tempest\Cache\Cache>[] */
    public array $caches = [];

    public ?bool $enable;

    public bool $projectCache = false;

    public bool $viewCache = false;

    public bool $discoveryCache = false;

    public function __construct(
        public string $directory = __DIR__ . '/../../../../.cache',
        public ?CacheItemPoolInterface $projectCachePool = null,

        /** Used as a global override, should be true in production, null in local */
        ?bool $enable = null,
    ) {
        $this->enable = $enable ?? env('CACHE') ?? null;
        $this->projectCache = env('PROJECT_CACHE', false);
        $this->viewCache = env('VIEW_CACHE', false);
        $this->discoveryCache = env('DISCOVERY_CACHE', false);
    }

    /** @param class-string<\Tempest\Cache\Cache> $className */
    public function addCache(string $className): void
    {
        $this->caches[] = $className;
    }
}
