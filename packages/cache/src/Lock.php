<?php

namespace Tempest\Cache;

use Closure;
use Stringable;
use Tempest\DateTime\DateTimeInterface;
use Tempest\DateTime\Duration;

interface Lock
{
    /**
     * The key used to identify the lock. This should be unique across all locks.
     */
    public string $key {
        get;
    }

    /**
     * The expiration date of the lock. If null, the lock will not expire.
     */
    public ?DateTimeInterface $expiration {
        get;
    }

    /**
     * The owner of the lock. This is used to verify that the lock is being released by the correct owner.
     */
    public string $owner {
        get;
    }

    /**
     * Attempts to acquire a lock.
     */
    public function acquire(): bool;

    /**
     * Checks if the lock is currently held.
     */
    public function locked(null|Stringable|string $by = null): bool;

    /**
     * Executes the given callback while holding the lock.
     *
     * @template TReturn
     *
     * @param Closure(): TReturn $callback The callback to execute while holding the lock.
     * @param null|DateTimeInterface|Duration $wait The time to wait for the lock to be acquired. If null, the lock will not wait.
     *
     * @return TReturn The result of the callback.
     */
    public function execute(Closure $callback, null|DateTimeInterface|Duration $wait = null): mixed;

    /**
     * Releases the lock.
     */
    public function release(bool $force = false): bool;
}
