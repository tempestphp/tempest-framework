<?php

declare(strict_types=1);

namespace Tempest\Generation\TypeScript;

use Tempest\Reflection\TypeReflector;

/**
 * Resolves PHP property types into TypeScript types.
 */
interface TypeResolver
{
    /**
     * Checks whether this resolver can handle the given type.
     */
    public function canResolve(TypeReflector $type): bool;

    /**
     * Resolves a PHP type into a TypeScript type string. May include a referenced class for cross-namespace resolution.
     */
    public function resolve(TypeReflector $type, TypeScriptGenerator $generator): ResolvedType;
}
