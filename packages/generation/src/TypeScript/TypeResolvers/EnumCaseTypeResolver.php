<?php

declare(strict_types=1);

namespace Tempest\Generation\TypeScript\TypeResolvers;

use BackedEnum;
use Tempest\Core\Priority;
use Tempest\Generation\TypeScript\TypeNodes\LiteralTypeNode;
use Tempest\Generation\TypeScript\TypeNodes\TypeNode;
use Tempest\Generation\TypeScript\TypeResolver;
use Tempest\Generation\TypeScript\TypeScriptGenerator;
use Tempest\Reflection\TypeReflector;

#[Priority(Priority::LOW)]
final class EnumCaseTypeResolver implements TypeResolver
{
    public function canResolve(TypeReflector $type): bool
    {
        return $type->isEnumCase();
    }

    public function resolve(TypeReflector $type, TypeScriptGenerator $generator): TypeNode
    {
        $case = $type->asEnumCase()->getValue();
        $value = $case instanceof BackedEnum
            ? $case->value
            : $case->name;

        return new LiteralTypeNode($value);
    }
}
