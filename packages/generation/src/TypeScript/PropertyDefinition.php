<?php

declare(strict_types=1);

namespace Tempest\Generation\TypeScript;

use Tempest\Generation\TypeScript\TypeNodes\TypeNode;

/**
 * Represents a property in a TypeScript interface.
 */
final readonly class PropertyDefinition
{
    /**
     * @param string $name The name of the property.
     * @param TypeNode $type The TypeScript type of the property.
     * @param bool $isNullable Whether the property is nullable.
     */
    public function __construct(
        public string $name,
        public TypeNode $type,
        public bool $isNullable,
    ) {}
}
