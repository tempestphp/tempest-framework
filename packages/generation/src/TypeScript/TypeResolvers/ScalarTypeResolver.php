<?php

declare(strict_types=1);

namespace Tempest\Generation\TypeScript\TypeResolvers;

use Tempest\Core\Priority;
use Tempest\Generation\TypeScript\ResolvedType;
use Tempest\Generation\TypeScript\TypeResolver;
use Tempest\Generation\TypeScript\TypeScriptGenerator;
use Tempest\Reflection\TypeReflector;

#[Priority(Priority::LOW)]
final class ScalarTypeResolver implements TypeResolver
{
    public function canResolve(TypeReflector $type): bool
    {
        return $type->isBuiltIn() && in_array($type->getName(), ['string', 'int', 'float', 'bool'], strict: true);
    }

    public function resolve(TypeReflector $type, TypeScriptGenerator $generator): ResolvedType
    {
        return new ResolvedType(match ($type->getName()) {
            'string' => 'string',
            'int', 'float' => 'number',
            'bool' => 'boolean',
        });
    }
}
