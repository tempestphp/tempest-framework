<?php

namespace Tempest\Cache;

use Closure;
use Stringable;
use Tempest\DateTime\DateTime;
use Tempest\DateTime\DateTimeInterface;
use Tempest\DateTime\Duration;

final class GenericLock implements Lock
{
    public function __construct(
        private(set) string $key,
        private(set) string $owner,
        private readonly Cache $cache,
        private(set) ?DateTimeInterface $expiration = null,
    ) {}

    public function locked(null|Stringable|string $by = null): bool
    {
        if ($by === null) {
            return $this->cache->has($this->key);
        }

        return $this->cache->get($this->key) === ((string) $by);
    }

    public function acquire(): bool
    {
        if ($this->locked()) {
            return false;
        }

        $this->cache->put(
            key: $this->key,
            value: $this->owner,
            expiration: $this->expiration,
        );

        return true;
    }

    public function execute(Closure $callback, null|DateTimeInterface|Duration $wait = null): mixed
    {
        $wait ??= Datetime::now();
        $waitUntil = ($wait instanceof Duration)
            ? DateTime::now()->plus($wait)
            : $wait;

        while (! $this->acquire()) {
            if ($waitUntil->beforeOrAtTheSameTime(DateTime::now())) {
                throw new LockAcquisitionTimedOut($this->key);
            }

            usleep(250); // TODO: sleep from clock?
        }

        try {
            return $callback();
        } finally {
            $this->release();
        }
    }

    public function release(bool $force = false): bool
    {
        if (! $this->locked()) {
            return false;
        }

        $lock = $this->cache->get($this->key);

        if ($lock !== $this->owner && ! $force) {
            return false;
        }

        $this->cache->remove($this->key);

        return true;
    }
}
