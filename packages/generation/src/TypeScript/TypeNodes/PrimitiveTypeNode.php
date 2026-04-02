<?php

declare(strict_types=1);

namespace Tempest\Generation\TypeScript\TypeNodes;

final class PrimitiveTypeNode implements TypeNode
{
    public function __construct(
        public readonly string $name,
    ) {}

    public array $references {
        get => [];
    }
}
