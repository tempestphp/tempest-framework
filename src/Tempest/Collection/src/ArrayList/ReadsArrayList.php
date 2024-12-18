<?php

declare(strict_types=1);

namespace Tempest\Collection\ArrayList;

/**
 * @template TValue
 */
trait ReadsArrayList
{
    /**
     * @var array<int,TValue>
     */
    private array $items = [];

    /**
     * @param array<int,TValue> $items
     */
    final public function __construct(array $items = [])
    {
        $this->items = $items;
    }

    /**
     * @param TValue $value
     */
    public function contains($value): bool
    {
        return in_array($value, $this->items, true);
    }

    public function indexOf($value): int|false
    {
        return array_search($value, $this->items, true);
    }

    public function each(callable $callback): self
    {
        foreach ($this->items as $item) {
            $callback($item);
        }

        return $this;
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->items[$offset] ?? null;
    }

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->items[$offset]);
    }

    abstract public function offsetSet(mixed $offset, $value): void;

    abstract public function offsetUnset(mixed $offset): void;
}
