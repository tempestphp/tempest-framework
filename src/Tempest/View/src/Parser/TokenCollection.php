<?php

namespace Tempest\View\Parser;

use ArrayIterator;
use IteratorAggregate;
use Traversable;

/**
 * @extends IteratorAggregate<\Tempest\View\Parser\Token>
 */
final class TokenCollection implements IteratorAggregate
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

    public function __debugInfo(): ?array
    {
        return [
            implode(
                ', ' . PHP_EOL,
                array_map(fn(Token $token) => $token->__debugInfo()[0], $this->tokens)
            ),
        ];
    }
}