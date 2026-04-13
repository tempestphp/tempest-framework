<?php

declare(strict_types=1);

namespace Tempest\Generation\TypeScript\TypeResolvers;

use Tempest\Generation\TypeScript\TypeNodes\SymbolTypeNode;
use Tempest\Generation\TypeScript\TypeNodes\TypeNode;
use Tempest\Generation\TypeScript\TypeResolver;
use Tempest\Generation\TypeScript\TypeScriptGenerator;
use Tempest\Reflection\TypeReflector;
use Tempest\Support\Priority;

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

    public function resolve(TypeReflector $type, TypeScriptGenerator $generator): TypeNode
    {
        $generator->include($type->getName());

        return new SymbolTypeNode($type->getName());
    }
}
