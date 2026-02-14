<?php

declare(strict_types=1);

namespace Tempest\Generation\TypeScript;

use Tempest\Reflection\TypeReflector;
use Tempest\Support\Str;

/**
 * Represents a TypeScript interface definition generated from a PHP class.
 */
final class InterfaceDefinition
{
    public string $namespace {
        get {
            if (! Str\contains($this->class, '\\')) {
                return '';
            }

            return Str\before_last($this->class, '\\');
        }
    }

    /**
     * @param PropertyDefinition[] $properties
     */
    public function __construct(
        public string $class,
        public TypeReflector $originalType,
        public array $properties,
    ) {}
}
