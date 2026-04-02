<?php

declare(strict_types=1);

namespace Tempest\Generation\TypeScript\TypeNodes;

final class SymbolTypeNode implements TypeNode
{
    public function __construct(
        public readonly string $fqcn,
    ) {}

    public array $references {
        get => [$this->fqcn];
    }
}
