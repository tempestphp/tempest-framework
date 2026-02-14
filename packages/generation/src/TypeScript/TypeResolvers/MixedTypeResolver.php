<?php

declare(strict_types=1);

namespace Tempest\Generation\TypeScript\TypeResolvers;

use Tempest\Core\Priority;
use Tempest\Generation\TypeScript\ResolvedType;
use Tempest\Generation\TypeScript\TypeResolver;
use Tempest\Generation\TypeScript\TypeScriptGenerator;
use Tempest\Reflection\TypeReflector;

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

    public function resolve(TypeReflector $type, TypeScriptGenerator $generator): ResolvedType
    {
        return new ResolvedType('any');
    }
}
