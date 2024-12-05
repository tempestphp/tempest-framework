<?php

namespace Tempest\Collection\Queue;

use Exception;

/**
 * @template TValue
 */
final class Queue
{
    /**
     * @var array<TValue>
     */
    private array $items = [];

    /**
     * @param array<TValue> $items
     */
    public function __construct(array $items = [])
    {
        foreach ($items as $item) {
            $this->enqueue($item);
        }
    }

    /**
     * @param TValue $item
     * @return self<TValue>
     */
    public function enqueue($item): self
    {
        $this->items[] = $item;

        return $this;
    }

    /**
     * @return TValue
     */
    public function dequeue()
    {
        $item = array_shift($this->items);

        if ($item === null) {
            // TODO: Update
            throw new Exception();
        }

        return $item;
    }

    /**
     * @return TValue
     */
    public function peek()
    {
        return $this->items[0] ?? throw new Exception();
    }

    /**
     * @param TValue $item
     */
    public function contains($item): bool
    {
        return in_array($item, $this->items, true);
    }

    /**
     * @return Queue<TValue>
     */
    public function clone(): Queue
    {
        return new Queue($this->items);
    }

    /**
     * @return array<TValue>
     */
    public function toArray(): array
    {
        return $this->items;
    }
}