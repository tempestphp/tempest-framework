<?php

declare(strict_types=1);

namespace Tempest\Generation\TypeScript\TypeResolvers;

use DateTimeInterface as NativeDateTimeInterface;
use Tempest\DateTime\DateTimeInterface;
use Tempest\Generation\TypeScript\TypeNodes\PrimitiveTypeNode;
use Tempest\Generation\TypeScript\TypeNodes\TypeNode;
use Tempest\Generation\TypeScript\TypeResolver;
use Tempest\Generation\TypeScript\TypeScriptGenerator;
use Tempest\Reflection\TypeReflector;
use Tempest\Support\Priority;

#[Priority(Priority::HIGH)]
final class DateTimeTypeResolver implements TypeResolver
{
    public function canResolve(TypeReflector $type): bool
    {
        return $type->matches(DateTimeInterface::class) || $type->matches(NativeDateTimeInterface::class);
    }

    public function resolve(TypeReflector $type, TypeScriptGenerator $generator): TypeNode
    {
        return new PrimitiveTypeNode('string');
    }
}
