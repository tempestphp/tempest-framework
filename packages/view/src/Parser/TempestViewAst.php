<?php

namespace Tempest\View\Parser;

use ArrayAccess;
use IteratorAggregate;
use Traversable;

final class TempestViewAst implements IteratorAggregate, ArrayAccess
{
    public function __construct(
        private(set) TokenCollection $tokens = new TokenCollection(),
    ) {}

    public function add(Token $token): self
    {
        $this->tokens->add($token);

        return $this;
    }

    public function compile(): string
    {
        return implode('', array_map(
            fn (Token $token) => $token->compile(),
            iterator_to_array($this->tokens),
        ));
    }

    public function getIterator(): Traversable
    {
        return $this->tokens->getIterator();
    }

    public function offsetExists(mixed $offset): bool
    {
        return $this->tokens->offsetExists($offset);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->tokens->offsetGet($offset);
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->tokens->offsetSet($offset, $value);
    }

    public function offsetUnset(mixed $offset): void
    {
        $this->tokens->offsetUnset($offset);
    }
}
