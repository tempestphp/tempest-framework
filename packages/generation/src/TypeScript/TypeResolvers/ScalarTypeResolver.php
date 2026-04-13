<?php

declare(strict_types=1);

namespace Tempest\Generation\TypeScript\TypeResolvers;

use LogicException;
use Tempest\Generation\TypeScript\TypeNodes\PrimitiveTypeNode;
use Tempest\Generation\TypeScript\TypeNodes\TypeNode;
use Tempest\Generation\TypeScript\TypeResolver;
use Tempest\Generation\TypeScript\TypeScriptGenerator;
use Tempest\Reflection\TypeReflector;
use Tempest\Support\Priority;

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

    public function resolve(TypeReflector $type, TypeScriptGenerator $generator): TypeNode
    {
        $type = self::SCALAR_TYPE_MAP[$type->getName()] ?? throw new LogicException(sprintf('Unsupported scalar type "%s".', $type->getName()));

        return new PrimitiveTypeNode($type);
    }
}
