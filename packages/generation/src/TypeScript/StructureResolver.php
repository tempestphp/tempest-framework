<?php

declare(strict_types=1);

namespace Tempest\Generation\TypeScript;

use Tempest\Reflection\TypeReflector;

interface StructureResolver
{
    /**
     * Resolves a PHP type into a TypeScript definition.
     */
    public function resolve(TypeReflector $type, TypeScriptGenerator $generator): TypeDefinition|InterfaceDefinition;
}
