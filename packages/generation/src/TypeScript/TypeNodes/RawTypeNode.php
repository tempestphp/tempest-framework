<?php

declare(strict_types=1);

namespace Tempest\Generation\TypeScript\TypeNodes;

final class RawTypeNode implements TypeNode
{
    public function __construct(
        public readonly string $expression,
    ) {}

    public array $references {
        get => [];
    }
}
