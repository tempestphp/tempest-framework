<?php

declare(strict_types=1);

namespace Tempest\Generation\TypeScript\TypeNodes;

final class ArrayTypeNode implements TypeNode
{
    public function __construct(
        public readonly TypeNode $type,
    ) {}

    public array $references {
        get => $this->type->references;
    }
}
