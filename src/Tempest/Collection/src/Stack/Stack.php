<?php

declare(strict_types=1);

namespace Tempest\Collection\Stack;

use Exception;
use LogicException;

/**
 * @template TValue
 */
final class Stack
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
            $this->push($item);
        }
    }

    /**
     * @param TValue $item
     * @return self<TValue>
     */
    public function push($item): Stack
    {
        $this->items[] = $item;

        return $this;
    }

    /**
     * @return TValue
     */
    public function pop()
    {
        $item = array_pop($this->items);

        if ($item === null) {
            // TODO: Update exception.
            throw new LogicException();
        }

        return $item;
    }

    /**
     * @return TValue
     */
    public function peek()
    {
        $item = $this->items[array_key_last($this->items)] ?? null;

        if ($item === null) {
            // TODO: Update exception.
            throw new Exception();
        }

        return $item;
    }

    /**
     * @param TValue $item
     * @return bool
     */
    public function contains($item): bool
    {
        return in_array($item, $this->items, true);
    }

    /**
     * @return Stack<TValue>
     */
    public function clone(): Stack
    {
        return new self($this->items);
    }

    public function toArray(): array
    {
        return $this->items;
    }
}
