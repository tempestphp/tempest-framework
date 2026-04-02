<?php

declare(strict_types=1);

namespace Tempest\Generation\TypeScript\TypeNodes;

final class IntersectionTypeNode implements TypeNode
{
    /**
     * @param TypeNode[] $types
     */
    public function __construct(
        public readonly array $types,
    ) {}

    public array $references {
        get {
            $fqcns = [];

            foreach ($this->types as $type) {
                $fqcns = [...$fqcns, ...$type->references];
            }

            return array_values(array_unique($fqcns));
        }
    }
}
