<?php

declare(strict_types=1);

namespace Tempest\Generation\TypeScript\StructureResolvers;

use BackedEnum;
use Tempest\Generation\TypeScript\StructureResolver;
use Tempest\Generation\TypeScript\TypeDefinition;
use Tempest\Generation\TypeScript\TypeScriptGenerator;
use Tempest\Reflection\TypeReflector;
use UnitEnum;

/**
 * Resolves PHP enums into TypeScript union types.
 */
final class EnumStructureResolver implements StructureResolver
{
    public function resolve(TypeReflector $type, TypeScriptGenerator $generator): TypeDefinition
    {
        $typeScriptType = implode(
            separator: ' | ',
            array: array_map(
                callback: function (UnitEnum $case) {
                    $value = $case instanceof BackedEnum
                        ? $case->value
                        : $case->name;

                    return is_string($value) ? "'{$value}'" : $value;
                },
                array: $type->asEnum()->getCases(),
            ),
        );

        return new TypeDefinition(
            class: $type->getName(),
            originalType: $type,
            definition: $typeScriptType,
            isNullable: $type->isNullable(),
        );
    }
}
