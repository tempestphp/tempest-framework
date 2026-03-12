<?php

namespace Tempest\Intl\MessageFormat\Parser\Node;

use Stringable;

final readonly class Identifier implements Node, Stringable
{
    public function __construct(
        public string $name,
        public ?string $namespace = null,
    ) {}

    public function __toString(): string
    {
        return $this->namespace ? "{$this->namespace}:{$this->name}" : $this->name;
    }
}
