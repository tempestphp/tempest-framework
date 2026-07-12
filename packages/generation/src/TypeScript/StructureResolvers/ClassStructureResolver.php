<?php

declare(strict_types=1);

namespace Tempest\Generation\TypeScript\StructureResolvers;

use Psr\Container\ContainerInterface;
use Tempest\Generation\TypeScript\InterfaceDefinition;
use Tempest\Generation\TypeScript\PropertyDefinition;
use Tempest\Generation\TypeScript\StructureResolver;
use Tempest\Generation\TypeScript\TypeNodes\ArrayTypeNode;
use Tempest\Generation\TypeScript\TypeNodes\IntersectionTypeNode;
use Tempest\Generation\TypeScript\TypeNodes\PrimitiveTypeNode;
use Tempest\Generation\TypeScript\TypeNodes\TypeNode;
use Tempest\Generation\TypeScript\TypeNodes\UnionTypeNode;
use Tempest\Generation\TypeScript\TypeScriptGenerationConfig;
use Tempest\Generation\TypeScript\TypeScriptGenerator;
use Tempest\Reflection\PropertyReflector;
use Tempest\Reflection\TypeReflector;

/**
 * Resolves PHP classes into TypeScript interfaces.
 */
final readonly class ClassStructureResolver implements StructureResolver
{
    public function __construct(
        private TypeScriptGenerationConfig $config,
        private ContainerInterface $container,
    ) {}

    public function resolve(TypeReflector $type, TypeScriptGenerator $generator): InterfaceDefinition
    {
        $class = $type->asClass();
        $properties = [];

        foreach ($class->getPublicProperties() as $property) {
            $properties[] = $this->resolveProperty($property, $generator);
        }

        return new InterfaceDefinition(
            class: $type->getName(),
            originalType: $type,
            properties: $properties,
        );
    }

    private function resolveProperty(PropertyReflector $property, TypeScriptGenerator $generator): PropertyDefinition
    {
        $type = $property->getType();

        if ($type->isIterable()) {
            $elementTypeReflector = $property->getIterableType();

            if ($elementTypeReflector instanceof TypeReflector) {
                $resolvedType = $this->resolveType($elementTypeReflector, $generator);

                return new PropertyDefinition(
                    name: $property->getName(),
                    type: new ArrayTypeNode($resolvedType),
                    isNullable: $property->isNullable(),
                );
            }

            return new PropertyDefinition(
                name: $property->getName(),
                type: new ArrayTypeNode(new PrimitiveTypeNode('any')),
                isNullable: $property->isNullable(),
            );
        }

        if ($type->isUnion() || $type->isIntersection()) {
            $parts = $type->split();
            $resolvedTypes = [];

            foreach ($parts as $part) {
                if ($part->getName() === 'null') {
                    continue;
                }

                $resolvedTypes[] = $this->resolveType($part, $generator);
            }

            return new PropertyDefinition(
                name: $property->getName(),
                type: match (true) {
                    $resolvedTypes === [] => new PrimitiveTypeNode('any'),
                    count($resolvedTypes) === 1 => $resolvedTypes[0],
                    $type->isIntersection() => new IntersectionTypeNode($resolvedTypes),
                    default => new UnionTypeNode($resolvedTypes),
                },
                isNullable: $property->isNullable(),
            );
        }

        $resolvedType = $this->resolveType($type, $generator);

        return new PropertyDefinition(
            name: $property->getName(),
            type: $resolvedType,
            isNullable: $property->isNullable(),
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

        return new PrimitiveTypeNode('any');
    }
}
