<?php

declare(strict_types=1);

namespace Tempest\Generation\TypeScript\TypeResolvers;

use Tempest\Core\Priority;
use Tempest\Generation\TypeScript\ResolvedType;
use Tempest\Generation\TypeScript\TypeResolver;
use Tempest\Generation\TypeScript\TypeScriptGenerator;
use Tempest\Reflection\TypeReflector;

/**
 * Resolves references to PHP enums into TypeScript type references.
 */
#[Priority(Priority::LOW)]
final class EnumReferenceTypeResolver implements TypeResolver
{
    public function canResolve(TypeReflector $type): bool
    {
        return $type->isEnum() && ! $type->isEnumCase();
    }

    public function resolve(TypeReflector $type, TypeScriptGenerator $generator): ResolvedType
    {
        $generator->include($type->getName());

        return new ResolvedType(
            type: $type->getShortName(),
            fqcn: $type->getName(),
        );
    }
}
