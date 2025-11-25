<?php

namespace Tempest\Testing;

use Closure;
use Tempest\Testing\Exceptions\TestHasFailed;
use Throwable;

final readonly class Tester
{
    public function __construct(
        private mixed $subject = null,
    ) {}

    public function fail(?string $reason = null): never
    {
        throw new TestHasFailed($reason ?? 'test was marked as failed');
    }

    public function is(mixed $expected): self
    {
        if ($expected !== $this->subject) {
            throw new TestHasFailed("failed asserting that %s is %s", $this->subject, $expected);
        }

        return $this;
    }

    public function equals(mixed $expected): self
    {
        if ($expected != $this->subject) {
            throw new TestHasFailed("failed asserting that %s equals %s", $this->subject, $expected);
        }

        return $this;
    }

    public function hasCount(int $expected): self
    {
        if ($expected !== count($this->subject)) {
            throw new TestHasFailed("failed asserting that array has %d items", $expected);
        }

        return $this;
    }

    public function contains(mixed $search): self
    {
        if (! in_array($search, $this->subject)) {
            throw new TestHasFailed("failed asserting that array contains %s", $search);
        }

        return $this;
    }

    public function hasKey(mixed $key): self
    {
        if (! array_key_exists($key, $this->subject)) {
            throw new TestHasFailed("failed asserting that array has key %s", $key);
        }

        return $this;
    }

    public function hasNoKey(mixed $key): self
    {
        if (array_key_exists($key, $this->subject)) {
            throw new TestHasFailed("failed asserting that array does not have key %s", $key);
        }

        return $this;
    }

    public function instanceOf(string $expectedClass): self
    {
        if (! $this->subject instanceof $expectedClass) {
            throw new TestHasFailed("failed asserting that %s is an instance of %s", $this->subject, $expectedClass);
        }
    }

    public function exceptionThrown(
        string $expectedExceptionClass,
        Closure $handler,
        ?Closure $exceptionTester = null,
    ): void
    {
        try {
            $handler();
        } catch (Throwable $throwable) {
            test($throwable)->instanceOf($expectedExceptionClass);

            if ($exceptionTester) {
                $exceptionTester($throwable);
            }

            return;
        }

        $this->fail("Expected exception {$expectedExceptionClass} was not thrown");
    }
}