<?php

declare(strict_types=1);

namespace Tempest\Generation\TypeScript\TypeNodes;

final class LiteralTypeNode implements TypeNode
{
    public function __construct(
        public readonly string|int|float|bool $value,
    ) {}

    public array $references {
        get => [];
    }
}
