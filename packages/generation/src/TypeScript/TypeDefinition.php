<?php

declare(strict_types=1);

namespace Tempest\Generation\TypeScript;

use Tempest\Generation\TypeScript\TypeNodes\TypeNode;
use Tempest\Reflection\TypeReflector;
use Tempest\Support\Str;

/**
 * Represents a TypeScript type alias definition.
 */
final class TypeDefinition
{
    public string $namespace {
        get {
            if (! Str\contains($this->class, '\\')) {
                return '';
            }

            return Str\before_last($this->class, '\\');
        }
    }

    public function __construct(
        public string $class,
        public TypeReflector $originalType,
        public TypeNode $type,
        public bool $isNullable,
    ) {}
}
