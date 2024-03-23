<?php

declare(strict_types=1);

namespace Tempest\Collection\Generic;

use Exception;
use Tempest\Collection\Queue;

final class GenericQueue implements Queue
{
    private array $items = [];

    public function __construct(array $items = [])
    {
        foreach ($items as $item) {
            $this->enqueue($item);
        }
    }

    public function enqueue(mixed $item): Queue
    {
        $this->items[] = $item;

        return $this;
    }

    public function dequeue(): mixed
    {
        $item = array_shift($this->items);

        if ($item === null) {
            throw new Exception();
        }

        return $item;
    }

    public function peek(): mixed
    {
        return $this->items[0] ?? throw new Exception();
    }

    public function contains(mixed $item): bool
    {
        return in_array($item, $this->items, true);
    }

    public function clone(): Queue
    {
        return new GenericQueue($this->items);
    }

    public function toArray(): array
    {
        return $this->items;
    }
}
