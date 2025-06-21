<?php

namespace Tempest\Internationalization\MessageFormat\Parser\Node;

final class Identifier implements Node
{
    public function __construct(
        public readonly string $name,
        public readonly ?string $namespace = null,
    ) {}

    public function __toString(): string
    {
        return $this->namespace ? "{$this->namespace}:{$this->name}" : $this->name;
    }
}
