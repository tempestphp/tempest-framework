<?php

namespace Tempest\Cache;

use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

final readonly class CacheConfig
{
    public function __construct(
        public CacheItemPoolInterface $pool = new FilesystemAdapter(
            namespace: '',
            defaultLifetime: 0,
            directory: __DIR__ . '/../../../../.cache',
        ),
    ) {}
}