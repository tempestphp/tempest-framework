<?php

declare(strict_types=1);

namespace Tempest\Generation\TypeScript;

/**
 * Represents a property in a TypeScript interface.
 */
final readonly class PropertyDefinition
{
    /**
     * @param string $name The name of the property.
     * @param string $definition The TypeScript definition of the property.
     * @param null|string $fqcn The PHP FQCN of the original type.
     */
    public function __construct(
        public string $name,
        public string $definition,
        public bool $isNullable,
        public ?string $fqcn = null,
    ) {}
}
