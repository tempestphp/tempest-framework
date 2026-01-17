<?php

declare(strict_types=1);

namespace Tempest\Generation\TypeScript\StructureResolvers;

use Tempest\Container\Container;
use Tempest\Generation\TypeScript\InterfaceDefinition;
use Tempest\Generation\TypeScript\PropertyDefinition;
use Tempest\Generation\TypeScript\ResolvedType;
use Tempest\Generation\TypeScript\StructureResolver;
use Tempest\Generation\TypeScript\TypeScriptGenerationConfig;
use Tempest\Generation\TypeScript\TypeScriptGenerator;
use Tempest\Reflection\PropertyReflector;
use Tempest\Reflection\TypeReflector;

/**
 * Resolves PHP classes into TypeScript interfaces.
 */
final class ClassStructureResolver implements StructureResolver
{
    public function __construct(
        private readonly TypeScriptGenerationConfig $config,
        private readonly Container $container,
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

            if ($elementTypeReflector !== null) {
                $result = $this->resolveType($elementTypeReflector, $generator);

                return new PropertyDefinition(
                    name: $property->getName(),
                    definition: $result->type . '[]',
                    isNullable: $property->isNullable(),
                    fqcn: $result->fqcn,
                );
            }

            return new PropertyDefinition(
                name: $property->getName(),
                definition: 'any[]',
                isNullable: $property->isNullable(),
            );
        }

        if ($type->isUnion() || $type->isIntersection()) {
            $parts = $type->split();
            $resolvedTypes = [];
            $referencedClasses = [];

            foreach ($parts as $part) {
                if ($part->getName() === 'null') {
                    continue;
                }

                $result = $this->resolveType($part, $generator);
                $resolvedTypes[] = $result->type;

                if ($result->fqcn !== null) {
                    $referencedClasses[] = $result->fqcn;
                }
            }

            $symbol = $type->isIntersection() ? '&' : '|';

            return new PropertyDefinition(
                name: $property->getName(),
                definition: implode(" {$symbol} ", $resolvedTypes),
                isNullable: $property->isNullable(),
                fqcn: count($referencedClasses) === 1 ? $referencedClasses[0] : null,
            );
        }

        $result = $this->resolveType($type, $generator);

        return new PropertyDefinition(
            name: $property->getName(),
            definition: $result->type,
            isNullable: $property->isNullable(),
            fqcn: $result->fqcn,
        );
    }

    private function resolveType(TypeReflector $type, TypeScriptGenerator $generator): ResolvedType
    {
        foreach ($this->config->resolvers as $resolverClass) {
            $resolver = $this->container->get($resolverClass);

            if ($resolver->canResolve($type)) {
                return $resolver->resolve($type, $generator);
            }
        }

        return new ResolvedType('any');
    }
}
