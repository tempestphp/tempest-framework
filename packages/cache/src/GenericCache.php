<?php

namespace Tempest\Cache;

use Closure;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Stringable;
use Tempest\Cache\Config\CacheConfig;
use Tempest\DateTime\DateTime;
use Tempest\DateTime\DateTimeInterface;
use Tempest\DateTime\Duration;
use Tempest\Support\Arr;

final class GenericCache implements Cache
{
    public function __construct(
        private(set) CacheConfig $cacheConfig,
        public bool $enabled = true,
        private ?CacheItemPoolInterface $adapter = null,
    ) {
        $this->adapter ??= $this->cacheConfig->createAdapter();
    }

    public function put(Stringable|string $key, mixed $value, null|Duration|DateTimeInterface $expiration = null): CacheItemInterface
    {
        $item = $this->adapter
            ->getItem((string) $key)
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

    public function putMany(iterable $values, null|Duration|DateTimeInterface $expiration = null): array
    {
        $items = [];

        foreach ($values as $key => $value) {
            $items[(string) $key] = $this->put($key, $value, $expiration);
        }
        return $items;
    }

    public function increment(Stringable|string $key, int $by = 1): int
    {
        if (! $this->enabled) {
            return 0;
        }

        $item = $this->adapter->getItem((string) $key);

        if (! $item->isHit()) {
            $item->set($by);
        } elseif (! is_numeric($item->get())) {
            throw new NotNumberException((string) $key);
        } else {
            $item->set(((int) $item->get()) + $by);
        }

        $this->adapter->save($item);

        return (int) $item->get();
    }

    public function decrement(Stringable|string $key, int $by = 1): int
    {
        if (! $this->enabled) {
            return 0;
        }

        $item = $this->adapter->getItem((string) $key);

        if (! $item->isHit()) {
            $item->set(-$by);
        } elseif (! is_numeric($item->get())) {
            throw new NotNumberException((string) $key);
        } else {
            $item->set(((int) $item->get()) - $by);
        }

        $this->adapter->save($item);

        return (int) $item->get();
    }

    public function get(Stringable|string $key): mixed
    {
        if (! $this->enabled) {
            return null;
        }

        return $this->adapter->getItem((string) $key)->get();
    }

    public function getMany(iterable $key): array
    {
        return Arr\map_with_keys(
            array: $key,
            map: fn (string|Stringable $key) => yield (string) $key => $this->adapter->getItem((string) $key)->get(),
        );
    }

    public function resolve(Stringable|string $key, Closure $callback, null|Duration|DateTimeInterface $expiration = null): mixed
    {
        if (! $this->enabled) {
            return $callback();
        }

        $item = $this->adapter->getItem((string) $key);

        if (! $item->isHit()) {
            $item = $this->put((string) $key, $callback(), $expiration);
        }

        return $item->get();
    }

    public function remove(Stringable|string $key): void
    {
        if (! $this->enabled) {
            return;
        }

        $this->adapter->deleteItem((string) $key);
    }

    public function clear(): void
    {
        if (! $this->adapter->clear()) {
            throw new CouldNotClearCache();
        }
    }
}
