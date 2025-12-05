<?php

namespace Tempest\View\Parser;

use ArrayAccess;
use ArrayIterator;
use IteratorAggregate;
use Traversable;

/**
 * @implements IteratorAggregate<\Tempest\View\Parser\Token>
 */
final class TokenCollection implements IteratorAggregate, ArrayAccess
{
    public function __construct(
        private array $tokens = [],
    ) {}

    public function add(Token $token): self
    {
        $this->tokens[] = $token;

        return $this;
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->tokens);
    }

    public function __debugInfo(): array
    {
        return [
            implode(
                ', ' . PHP_EOL,
                array_map(fn (Token $token) => $token->__debugInfo()[0], $this->tokens),
            ),
        ];
    }

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->tokens[$offset]);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->tokens[$offset] ?? null;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->tokens[$offset] = $value;
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->tokens[$offset]);
    }
}
