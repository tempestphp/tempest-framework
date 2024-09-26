<?php

declare(strict_types=1);

namespace Tempest\Cache;

use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

final class CacheConfig
{
    /** @var class-string<\Tempest\Cache\Cache>[] */
    public array $caches = [];

    public function __construct(
        public CacheItemPoolInterface $pool = new FilesystemAdapter(
            namespace: '',
            defaultLifetime: 0,
            directory: __DIR__ . '/../../../../.cache',
        ),
    ) {
    }

    /** @param class-string<\Tempest\Cache\Cache> $className */
    public function addCache(string $className): void
    {
        $this->caches[] = $className;
    }
}
