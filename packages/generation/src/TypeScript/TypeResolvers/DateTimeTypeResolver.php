<?php

declare(strict_types=1);

namespace Tempest\Generation\TypeScript\TypeResolvers;

use DateTimeInterface as NativeDateTimeInterface;
use Tempest\Core\Priority;
use Tempest\DateTime\DateTimeInterface;
use Tempest\Generation\TypeScript\ResolvedType;
use Tempest\Generation\TypeScript\TypeResolver;
use Tempest\Generation\TypeScript\TypeScriptGenerator;
use Tempest\Reflection\TypeReflector;

#[Priority(Priority::HIGH)]
final class DateTimeTypeResolver implements TypeResolver
{
    public function canResolve(TypeReflector $type): bool
    {
        return $type->matches(DateTimeInterface::class) || $type->matches(NativeDateTimeInterface::class);
    }

    public function resolve(TypeReflector $type, TypeScriptGenerator $generator): ResolvedType
    {
        return new ResolvedType('string');
    }
}
