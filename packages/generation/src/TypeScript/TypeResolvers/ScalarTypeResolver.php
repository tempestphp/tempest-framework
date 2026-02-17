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
    private const array SCALAR_TYPE_MAP = [
        'string' => 'string',
        'int' => 'number',
        'float' => 'number',
        'bool' => 'boolean',
    ];

    public function canResolve(TypeReflector $type): bool
    {
        return $type->isBuiltIn() && isset(self::SCALAR_TYPE_MAP[$type->getName()]);
    }

    public function resolve(TypeReflector $type, TypeScriptGenerator $generator): ResolvedType
    {
        $type = self::SCALAR_TYPE_MAP[$type->getName()] ?? throw new \LogicException(sprintf('Unsupported scalar type "%s".', $type->getName()));

        return new ResolvedType($type);
    }
}
