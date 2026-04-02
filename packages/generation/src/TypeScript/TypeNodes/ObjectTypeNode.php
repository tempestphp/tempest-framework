<?php

declare(strict_types=1);

namespace Tempest\Generation\TypeScript\TypeNodes;

final class ObjectTypeNode implements TypeNode
{
    /**
     * @param ObjectTypePropertyNode[] $properties
     */
    public function __construct(
        public readonly array $properties,
    ) {}

    public array $references {
        get {
            $references = [];

            foreach ($this->properties as $property) {
                $references = [...$references, ...$property->type->references];
            }

            return array_values(array_unique($references));
        }
    }
}
