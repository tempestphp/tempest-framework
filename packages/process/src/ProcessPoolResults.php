<?php

namespace Tempest\Process;

use ArrayAccess;
use Countable;
use Iterator;
use Tempest\Support\Arr\ImmutableArray;

final class ProcessPoolResults implements Iterator, ArrayAccess, Countable
{
    public function __construct(
        /** @var ImmutableArray<ProcessResult> */
        private ImmutableArray $results,
    ) {}

    /**
     * Determines whether all results in the pool were successful.
     */
    public function allSuccessful(): bool
    {
        return $this->results->every(fn (ProcessResult $result) => $result->successful());
    }

    /**
     * Determines whether all results in the pool failed.
     */
    public function allFailed(): bool
    {
        return $this->results->every(fn (ProcessResult $result) => $result->failed());
    }

    /**
     * Determines whether there are any successful results in the pool.
     */
    public function someSuccessful(): bool
    {
        return $this->results->filter(fn (ProcessResult $result) => $result->successful())->count() > 0;
    }

    /**
     * Determines whether there are any failed results in the pool.
     */
    public function someFailed(): bool
    {
        return $this->results->filter(fn (ProcessResult $result) => $result->failed())->count() > 0;
    }

    /**
     * Returns all results that were successful.
     */
    public function successful(): ImmutableArray
    {
        return $this->results->filter(fn (ProcessResult $result) => $result->successful());
    }

    /**
     * Returns all results that failed.
     */
    public function failed(): ImmutableArray
    {
        return $this->results->filter(fn (ProcessResult $result) => ! $result->successful());
    }

    public function toImmutableArray(): ImmutableArray
    {
        return $this->results;
    }

    public function toArray(): array
    {
        return $this->results->toArray();
    }

    public function current(): ProcessResult
    {
        return $this->results->current();
    }

    public function next(): void
    {
        $this->results->next();
    }

    public function key(): int|string
    {
        return $this->results->key();
    }

    public function valid(): bool
    {
        return $this->results->valid();
    }

    public function rewind(): void
    {
        $this->results->rewind();
    }

    public function offsetExists(mixed $offset): bool
    {
        return $this->results->offsetExists($offset);
    }

    public function offsetGet(mixed $offset): ProcessResult
    {
        return $this->results->offsetGet($offset);
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        throw new \BadMethodCallException('ProcessPoolResults is immutable and cannot be modified.');
    }

    public function offsetUnset(mixed $offset): void
    {
        throw new \BadMethodCallException('ProcessPoolResults is immutable and cannot be modified.');
    }

    public function count(): int
    {
        return $this->results->count();
    }
}
