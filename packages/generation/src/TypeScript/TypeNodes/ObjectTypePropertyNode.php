<?php

declare(strict_types=1);

namespace Tempest\Generation\TypeScript\TypeNodes;

final readonly class ObjectTypePropertyNode
{
    public function __construct(
        public string $name,
        public TypeNode $type,
        public bool $optional = false,
    ) {}
}
