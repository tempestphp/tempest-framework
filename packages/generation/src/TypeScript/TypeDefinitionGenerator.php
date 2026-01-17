<?php

namespace Tempest\Generation\TypeScript;

use Tempest\Reflection\TypeReflector;

/**
 * Reponsible for generating TypeScript type definitions out of a {@see TypeReflector}.
 */
interface TypeDefinitionGenerator
{
    /**
     * Checks whether this generator can handle the given type.
     */
    public function canGenerate(TypeReflector $type): bool;

    /**
     * Generates a {@see TypeDefinition} for the given type.
     */
    public function generate(TypeReflector $type): TypeDefinition;
}
