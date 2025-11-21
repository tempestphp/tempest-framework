<?php

namespace Tempest\Testing;

use Tempest\Testing\Exceptions\TestHasFailed;

final readonly class Tester
{
    public function __construct(
        private mixed $subject = null,
    ) {}

    public function fail(): never
    {
        throw new TestHasFailed('test was marked as failed');
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
}