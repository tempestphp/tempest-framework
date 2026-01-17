<?php

declare(strict_types=1);

namespace Tempest\Generation\TypeScript\TypeResolvers;

use Tempest\Core\Priority;
use Tempest\Generation\TypeScript\ResolvedType;
use Tempest\Generation\TypeScript\TypeResolver;
use Tempest\Generation\TypeScript\TypeScriptGenerator;
use Tempest\Reflection\TypeReflector;

/**
 * Resolves references to PHP classes and interfaces into TypeScript type references.
 */
#[Priority(Priority::NORMAL)]
final class ClassReferenceTypeResolver implements TypeResolver
{
    public function canResolve(TypeReflector $type): bool
    {
        return ($type->isClass() || $type->isInterface()) && ! $type->isEnum();
    }

    public function resolve(TypeReflector $type, TypeScriptGenerator $generator): ResolvedType
    {
        $generator->include($type->getName());

        return new ResolvedType(
            type: $type->asClass()->getShortName(),
            fqcn: $type->getName(),
        );
    }
}
