<?php

declare(strict_types=1);

namespace Tempest\Collection\Generic;

use Exception;
use Tempest\Collection\Stack;

final class GenericStack implements Stack
{
    private array $items = [];

    public function __construct(array $items = [])
    {
        foreach ($items as $item) {
            $this->push($item);
        }
    }

    public function push(mixed $item): Stack
    {
        array_push($this->items, $item);

        return $this;
    }

    public function pop(): mixed
    {
        $item = array_pop($this->items);

        if ($item === null) {
            // TODO: Update exception.
            throw new Exception();
        }

        return $item;
    }

    public function peek(): mixed
    {
        $item = $this->items[array_key_last($this->items)] ?? null;

        if ($item === null) {
            // TODO: Update exception.
            throw new Exception();
        }

        return $item;
    }

    public function contains(mixed $item): bool
    {
        return in_array($item, $this->items, true);
    }

    public function clone(): Stack
    {
        return new GenericStack($this->items);
    }

    public function toArray(): array
    {
        return $this->items;
    }
}
