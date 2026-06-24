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

    public ?Duration $duration {
        get => $this->lock->duration;
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

    public function locked(Stringable|string|null $by = null): bool
    {
        return $this->lock->locked($by);
    }

    public function execute(Closure $callback, DateTimeInterface|Duration|null $wait = null): mixed
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
    public function assertLocked(Stringable|string|null $by = null, DateTimeInterface|Duration|null $for = null): self
    {
        Assert::assertTrue(
            condition: $this->locked($by),
            message: $by
                ? "Lock `{$this->key}` is not being held by `{$by}`."
                : "Lock `{$this->key}` is not being held.",
        );

        if ($for !== null) {
            if ($for instanceof DateTimeInterface) {
                $for = $for->since(DateTime::now());
            }

            Assert::assertNotNull(
                actual: $this->duration,
                message: "Expected lock `{$this->key}` to have a duration, but it has none.",
            );

            Assert::assertTrue(
                condition: $this->duration->getTotalSeconds() >= $for->getTotalSeconds(),
                message: "Expected lock `{$this->key}` to have a duration of at least `{$for->getTotalSeconds()}` seconds, but it has `{$this->duration->getTotalSeconds()}` seconds.",
            );
        }

        return $this;
    }

    /**
     * Asserts that the specified lock is not being held.
     */
    public function assertNotLocked(Stringable|string|null $by = null): self
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
