<?php

namespace Tempest\View\Parser;

use ArrayIterator;
use IteratorAggregate;
use Traversable;

final readonly class TokenCollection implements IteratorAggregate
{
    public function __construct(
        private array $items,
    ) {}

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->items);
    }

    public function __debugInfo(): ?array
    {
        return [
            implode(
                ', ' . PHP_EOL,
                array_map(fn(Token $token) => $token->__debugInfo()[0], $this->items)
            ),
        ];
    }
}