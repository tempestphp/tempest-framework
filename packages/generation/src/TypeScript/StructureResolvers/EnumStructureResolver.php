<?php

declare(strict_types=1);

namespace Tempest\Generation\TypeScript\StructureResolvers;

use ReflectionEnumBackedCase;
use ReflectionEnumUnitCase;
use RuntimeException;
use Tempest\Container\Container;
use Tempest\Generation\TypeScript\StructureResolver;
use Tempest\Generation\TypeScript\TypeDefinition;
use Tempest\Generation\TypeScript\TypeScriptGenerationConfig;
use Tempest\Generation\TypeScript\TypeScriptGenerator;
use Tempest\Reflection\TypeReflector;

/**
 * Resolves PHP enums into TypeScript union types.
 */
final class EnumStructureResolver implements StructureResolver
{
    public function __construct(
        private readonly TypeScriptGenerationConfig $config,
        private readonly Container $container,
    ) {}

    public function resolve(TypeReflector $type, TypeScriptGenerator $generator): TypeDefinition
    {
        $typeScriptType = implode(
            separator: ' | ',
            array: array_map(
                callback: fn (ReflectionEnumUnitCase|ReflectionEnumBackedCase $case) => $this->resolveType(new TypeReflector($case), $generator),
                array: $type->asEnum()->getReflectionCases(),
            ),
        );

        return new TypeDefinition(
            class: $type->getName(),
            originalType: $type,
            definition: $typeScriptType,
            isNullable: $type->isNullable(),
        );
    }

    private function resolveType(TypeReflector $type, TypeScriptGenerator $generator): string
    {
        foreach ($this->config->resolvers as $resolverClass) {
            $resolver = $this->container->get($resolverClass);

            if ($resolver->canResolve($type)) {
                return $resolver->resolve($type, $generator)->type;
            }
        }

        throw new RuntimeException('No suitable type resolver found for type: ' . $type->getName());
    }
}
