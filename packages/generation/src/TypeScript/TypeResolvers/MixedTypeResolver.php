<?php

declare(strict_types=1);

namespace Tempest\Generation\TypeScript\TypeResolvers;

use Tempest\Generation\TypeScript\TypeNodes\PrimitiveTypeNode;
use Tempest\Generation\TypeScript\TypeNodes\TypeNode;
use Tempest\Generation\TypeScript\TypeResolver;
use Tempest\Generation\TypeScript\TypeScriptGenerator;
use Tempest\Reflection\TypeReflector;
use Tempest\Support\Priority;

/**
 * Fallback resolver for unhandled types.
 */
#[Priority(Priority::LOWEST)]
final class MixedTypeResolver implements TypeResolver
{
    public function canResolve(TypeReflector $type): bool
    {
        return true;
    }

    public function resolve(TypeReflector $type, TypeScriptGenerator $generator): TypeNode
    {
        return new PrimitiveTypeNode('any');
    }
}
