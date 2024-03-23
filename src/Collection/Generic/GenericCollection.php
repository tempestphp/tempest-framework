<?php

declare(strict_types=1);

namespace Tempest\Collection\Generic;

use Exception;
use Tempest\Collection\Collection;

final class GenericCollection implements Collection
{
    private string|int $index = 0;

    private array $items = [];

    public function __construct(array $items = [])
    {
        $this->items = $items;
    }

    public function get(mixed $key): mixed
    {
        return $this->items[$key];
    }

    public function has(mixed $key): bool
    {
        return isset($this->items[$key]);
    }

    public function add(mixed $item): Collection
    {
        $this->items[] = $item;

        return $this;
    }

    public function set(mixed $key, mixed $value): Collection
    {
        $this->items[$key] = $value;

        return $this;
    }

    /**
     * @throws Exception
     */
    public function remove(mixed $item): Collection
    {
        return $this->removeAt(
            $this->indexOf($item)
        );
    }

    public function removeAt(mixed $key): Collection
    {
        unset($this->items[$key]);

        return $this;
    }

    public function indexOf(mixed $item): string|int
    {
        $item = array_search($item, $this->items, true);

        // TODO: Update
        if ($item === false) {
            throw new Exception();
        }

        return $item;
    }

    public function current(): mixed
    {
        return current($this->items);
    }

    public function next(): void
    {
        next($this->items);
    }

    public function key(): mixed
    {
        return key($this->items);
    }

    public function valid(): bool
    {
        return isset(
            $this->items[$this->key()]
        );
    }

    public function rewind(): void
    {
        reset($this->items);
    }

    public function offsetExists(mixed $offset): bool
    {
        return $this->has($offset);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->get($offset);
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->set($offset, $value);
    }

    public function offsetUnset(mixed $offset): void
    {
        $this->removeAt($offset);
    }
}
