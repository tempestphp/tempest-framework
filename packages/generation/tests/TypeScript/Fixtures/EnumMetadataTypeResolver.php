<?php

declare(strict_types=1);

namespace Tempest\Generation\Tests\TypeScript\Fixtures;

use BackedEnum;
use ReflectionAttribute as PHPReflectionAttribute;
use Tempest\Generation\Tests\TypeScript\Fixtures\Metadata\EnumMetadata;
use Tempest\Generation\TypeScript\TypeNodes\LiteralTypeNode;
use Tempest\Generation\TypeScript\TypeNodes\ObjectTypeNode;
use Tempest\Generation\TypeScript\TypeNodes\ObjectTypePropertyNode;
use Tempest\Generation\TypeScript\TypeNodes\TypeNode;
use Tempest\Generation\TypeScript\TypeResolver;
use Tempest\Generation\TypeScript\TypeScriptGenerator;
use Tempest\Reflection\TypeReflector;

final class EnumMetadataTypeResolver implements TypeResolver
{
    public function canResolve(TypeReflector $type): bool
    {
        if (! $type->isEnumCase()) {
            return false;
        }

        return $type->asEnumCase()->getAttributes(EnumMetadata::class, PHPReflectionAttribute::IS_INSTANCEOF) !== [];
    }

    public function resolve(TypeReflector $type, TypeScriptGenerator $generator): TypeNode
    {
        $enumCase = $type->asEnumCase()->getValue();
        $caseValue = $enumCase instanceof BackedEnum
            ? $enumCase->value
            : $enumCase->name;

        $properties = [
            new ObjectTypePropertyNode(
                name: 'value',
                type: new LiteralTypeNode((string) $caseValue),
            ),
        ];

        foreach ($type->asEnumCase()->getAttributes(EnumMetadata::class, PHPReflectionAttribute::IS_INSTANCEOF) as $attribute) {
            /** @var EnumMetadata $metadata */
            $metadata = $attribute->newInstance();

            $properties[] = new ObjectTypePropertyNode(
                name: $metadata->key,
                type: new LiteralTypeNode($metadata->value),
            );
        }

        return new ObjectTypeNode($properties);
    }
}
