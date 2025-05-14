<?php

namespace Tempest\Cache;

use Closure;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Tempest\Cache\Config\CacheConfig;
use Tempest\DateTime\DateTime;
use Tempest\DateTime\DateTimeInterface;
use Tempest\DateTime\Duration;

final class GenericCache implements Cache
{
    public function __construct(
        private(set) CacheConfig $cacheConfig,
        public bool $enabled = true,
        private ?CacheItemPoolInterface $adapter = null,
    ) {
        $this->adapter ??= $this->cacheConfig->createAdapter();
    }

    public function put(string $key, mixed $value, null|Duration|DateTimeInterface $expiration = null): CacheItemInterface
    {
        $item = $this->adapter
            ->getItem($key)
            ->set($value);

        if ($expiration instanceof Duration) {
            $expiration = DateTime::now()->plus($expiration);
        }

        if ($expiration !== null) {
            $item = $item->expiresAt($expiration->toNativeDateTime());
        }

        if ($this->enabled) {
            $this->adapter->save($item);
        }

        return $item;
    }

    public function get(string $key): mixed
    {
        if (! $this->enabled) {
            return null;
        }

        return $this->adapter->getItem($key)->get();
    }

    public function resolve(string $key, Closure $callback, null|Duration|DateTimeInterface $expiration = null): mixed
    {
        if (! $this->enabled) {
            return $callback();
        }

        $item = $this->adapter->getItem($key);

        if (! $item->isHit()) {
            $item = $this->put($key, $callback(), $expiration);
        }

        return $item->get();
    }

    public function remove(string $key): void
    {
        if (! $this->enabled) {
            return;
        }

        $this->adapter->deleteItem($key);
    }

    public function clear(): void
    {
        if (! $this->adapter->clear()) {
            throw new CouldNotClearCache();
        }
    }
}
