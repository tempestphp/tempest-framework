<?php

namespace Tempest\Intl\MessageFormat\Parser\Node;

final readonly class Identifier implements Node
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
