<?php

declare(strict_types=1);

namespace Tempest\Cache;

use Psr\Cache\CacheItemPoolInterface;
use function Tempest\env;

final class CacheConfig
{
    /** @var class-string<\Tempest\Cache\Cache>[] */
    public array $caches = [];

    public bool $enabled;

    public function __construct(
        ?bool $enabled = null,
        public string $directory = __DIR__ . '/../../../../.cache',
        public ?CacheItemPoolInterface $projectCachePool = null,
    ) {
        $this->enabled = $enabled ?? env('CACHE', true);
    }

    /** @param class-string<\Tempest\Cache\Cache> $className */
    public function addCache(string $className): void
    {
        $this->caches[] = $className;
    }
}
