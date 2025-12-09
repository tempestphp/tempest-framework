<?php

declare(strict_types=1);

namespace Tempest\Mapper;

use Closure;
use Tempest\Container\Container;
use Tempest\Container\Singleton;
use Tempest\Reflection\FunctionReflector;
use Tempest\Reflection\PropertyReflector;
use Tempest\Reflection\TypeReflector;
use UnitEnum;

#[Singleton]
final class CasterFactory
{
    /**
     * @var array<string, array{string|Closure, class-string<\Tempest\Mapper\Caster>|Closure, int}[]>
     */
    private(set) array $casters = [];

    private(set) Context|UnitEnum|string|null $context = null;

    public function __construct(
        private readonly Container $container,
    ) {}

    /**
     * @param class-string<\Tempest\Mapper\Caster> $casterClass
     */
    public function addCaster(string|array|Closure $for, string|Closure $casterClass, int $priority = 0, Context|UnitEnum|string|null $context = null): self
    {
        $context = MappingContext::from($context);

        $this->casters[$context->key] ??= [];
        $this->casters[$context->key][] = [$for, $casterClass, $priority];

        usort($this->casters[$context->key], static fn (array $a, array $b) => $a[2] <=> $b[2]);

        return $this;
    }

    /**
     * Sets the context that should be passed to casters.
     */
    public function in(Context|UnitEnum|string $context): self
    {
        $serializer = clone $this;
        $serializer->context = $context;

        return $serializer;
    }

    public function forProperty(PropertyReflector $property): ?Caster
    {
        $context = MappingContext::from($this->context);
        $type = $property->getType();
        $castWith = $property->getAttribute(CastWith::class);

        if ($castWith === null && $type->isClass()) {
            $castWith = $type->asClass()->getAttribute(CastWith::class, recursive: true);
        }

        if ($castWith) {
            return $this->container->get($castWith->className, context: $context);
        }

        if ($casterAttribute = $property->getAttribute(ProvidesCaster::class)) {
            return $this->container->get($casterAttribute->caster, context: $context);
        }

        foreach ($this->resolveCasters() as [$for, $casterClass]) {
            if (! $this->casterMatches($for, $property)) {
                continue;
            }

            if (is_a($casterClass, DynamicCaster::class, allow_string: true)) {
                return $casterClass::make($property, $context);
            }

            return $this->container->get($casterClass, context: $context);
        }

        return null;
    }

    private function casterMatches(Closure|string|array $for, PropertyReflector $property): bool
    {
        if (is_array($for)) {
            return array_any($for, fn (Closure|string $forType) => $this->casterMatches($forType, $property));
        }

        $type = $property->getType();

        if (is_callable($for)) {
            $parameter = new FunctionReflector($for)->getParameter(key: 0);

            if ($parameter?->getType()->getName() === PropertyReflector::class) {
                return $for($property);
            }

            if ($parameter?->getType()->getName() === TypeReflector::class) {
                return $for($type);
            }
        }

        if (is_string($for) && $type->matches($for)) {
            return true;
        }

        if ($type->getName() === $for) {
            return true;
        }

        return false;
    }

    /**
     * @return array{string|Closure,class-string<\Tempest\Mapper\Caster>|Closure,int}[]
     */
    private function resolveCasters(): array
    {
        return [
            ...($this->casters[MappingContext::from($this->context)->key] ?? []),
            ...($this->casters[MappingContext::default()->key] ?? []),
        ];
    }
}
