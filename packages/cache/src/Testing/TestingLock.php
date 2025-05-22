<?php

namespace Tempest\Cache\Testing;

use Closure;
use PHPUnit\Framework\Assert;
use Stringable;
use Tempest\Cache\GenericLock;
use Tempest\Cache\Lock;
use Tempest\DateTime\DateTime;
use Tempest\DateTime\DateTimeInterface;
use Tempest\DateTime\Duration;

final class TestingLock implements Lock
{
    public string $key {
        get => $this->lock->key;
    }

    public ?DateTimeInterface $expiration {
        get => $this->lock->expiration;
    }

    public string $owner {
        get => $this->lock->owner;
    }

    public function __construct(
        private readonly GenericLock $lock,
    ) {}

    public function acquire(): bool
    {
        return $this->lock->acquire();
    }

    public function locked(null|Stringable|string $by = null): bool
    {
        return $this->lock->locked($by);
    }

    public function execute(Closure $callback, null|DateTimeInterface|Duration $wait = null): mixed
    {
        return $this->lock->execute($callback, $wait);
    }

    public function release(bool $force = false): bool
    {
        return $this->lock->release($force);
    }

    /**
     * Asserts that the specified lock is being held.
     */
    public function assertLocked(null|Stringable|string $by = null, null|DateTimeInterface|Duration $until = null): self
    {
        Assert::assertTrue(
            condition: $this->locked($by),
            message: $by
                ? "Lock `{$this->key}` is not being held by `{$by}`."
                : "Lock `{$this->key}` is not being held.",
        );

        if ($until) {
            if ($until instanceof Duration) {
                $until = DateTime::now()->plus($until);
            }

            Assert::assertTrue(
                condition: $this->expiration->afterOrAtTheSameTime($until),
                message: "Expected lock `{$this->key}` to expire at or after `{$until}`, but it expires at `{$this->expiration}`.",
            );
        }

        return $this;
    }

    /**
     * Asserts that the specified lock is not being held.
     */
    public function assertNotLocked(null|Stringable|string $by = null): self
    {
        Assert::assertFalse(
            condition: $this->locked($by),
            message: $by
                ? "Lock `{$this->key}` is being held by `{$by}`."
                : "Lock `{$this->key}` is being held.",
        );

        return $this;
    }
}
