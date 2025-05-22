<?php

namespace Tempest\Core;

use Closure;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Tempest\DateTime\DateTime;
use Tempest\DateTime\DateTimeInterface;
use Tempest\DateTime\Duration;

use function Tempest\internal_storage_path;

final class ConfigCache
{
    public function __construct(
        public bool $enabled = false,
        private ?CacheItemPoolInterface $pool = null,
    ) {
        $this->pool ??= new FilesystemAdapter(
            directory: internal_storage_path('cache/config'),
        );
    }

    public function clear(): void
    {
        $this->pool->clear();
    }

    public function put(string $key, mixed $value, null|Duration|DateTimeInterface $expiresAt = null): CacheItemInterface
    {
        $item = $this->pool
            ->getItem($key)
            ->set($value);

        if ($expiresAt instanceof Duration) {
            $expiresAt = DateTime::now()->plus($expiresAt);
        }

        if ($expiresAt !== null) {
            $item = $item->expiresAt($expiresAt->toNativeDateTime());
        }

        if ($this->enabled) {
            $this->pool->save($item);
        }

        return $item;
    }

    public function resolve(string $key, Closure $callback, null|Duration|DateTimeInterface $expiresAt = null): mixed
    {
        if (! $this->enabled) {
            return $callback();
        }

        $item = $this->pool->getItem($key);

        if (! $item->isHit()) {
            $item = $this->put($key, $callback(), $expiresAt);
        }

        return $item->get();
    }
}
