<?php

declare(strict_types=1);

namespace Tempest\Generation\TypeScript\StructureResolvers;

use Psr\Container\ContainerInterface;
use ReflectionEnumBackedCase;
use ReflectionEnumUnitCase;
use RuntimeException;
use Tempest\Generation\TypeScript\StructureResolver;
use Tempest\Generation\TypeScript\TypeDefinition;
use Tempest\Generation\TypeScript\TypeNodes\TypeNode;
use Tempest\Generation\TypeScript\TypeNodes\UnionTypeNode;
use Tempest\Generation\TypeScript\TypeScriptGenerationConfig;
use Tempest\Generation\TypeScript\TypeScriptGenerator;
use Tempest\Reflection\TypeReflector;

/**
 * Resolves PHP enums into TypeScript union types.
 */
final readonly class EnumStructureResolver implements StructureResolver
{
    public function __construct(
        private TypeScriptGenerationConfig $config,
        private ContainerInterface $container,
    ) {}

    public function resolve(TypeReflector $type, TypeScriptGenerator $generator): TypeDefinition
    {
        $types = array_map(
            callback: fn (ReflectionEnumUnitCase|ReflectionEnumBackedCase $case) => $this->resolveType(new TypeReflector($case), $generator),
            array: $type->asEnum()->getReflectionCases(),
        );

        $resolvedType = count($types) === 1
            ? $types[0]
            : new UnionTypeNode($types);

        return new TypeDefinition(
            class: $type->getName(),
            originalType: $type,
            type: $resolvedType,
            isNullable: $type->isNullable(),
        );
    }

    private function resolveType(TypeReflector $type, TypeScriptGenerator $generator): TypeNode
    {
        foreach ($this->config->resolvers as $resolverClass) {
            $resolver = $this->container->get($resolverClass);

            if ($resolver->canResolve($type)) {
                return $resolver->resolve($type, $generator);
            }
        }

        throw new RuntimeException('No suitable type resolver found for type: ' . $type->getName());
    }
}
