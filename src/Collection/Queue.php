<?php

declare(strict_types=1);

namespace Tempest\Collection;

/**
 * @template TValue
 */
interface Queue
{
    /**
     * @param TValue $item
     * @return self<TValue>
     */
    public function enqueue(mixed $item): self;

    /**
     * @return TValue
     */
    public function dequeue(): mixed;

    /**
     * @return TValue
     */
    public function peek(): mixed;

    /**
     * @param TValue $item
     * @return bool
     */
    public function contains(mixed $item): bool;

    /**
     * Clones the items in the existing queue to a new queue object.
     *
     * @return self<TValue>
     */
    public function clone(): self;

    /**
     * Returns an array of enqueued items.
     *
     * @return array<int,TValue>
     */
    public function toArray(): array;
}
