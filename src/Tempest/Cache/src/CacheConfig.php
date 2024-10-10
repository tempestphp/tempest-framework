<?php

declare(strict_types=1);

namespace Tempest\Cache;

use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use function Tempest\env;

final class CacheConfig
{
    /** @var class-string<\Tempest\Cache\Cache>[] */
    public array $caches = [];

    public bool $enabled;

    public function __construct(
        public CacheItemPoolInterface $pool = new FilesystemAdapter(
            namespace: '',
            defaultLifetime: 0,
            directory: __DIR__ . '/../../../../.cache',
        ),
        ?bool $enabled = null,
    ) {
        $this->enabled = $enabled ?? env('CACHE', true);
    }

    /** @param class-string<\Tempest\Cache\Cache> $className */
    public function addCache(string $className): void
    {
        $this->caches[] = $className;
    }
}
