<?php

declare(strict_types=1);

namespace Tempest\Mapper;

use Closure;
use Tempest\Container\Container;
use Tempest\Container\Singleton;
use Tempest\Mapper\Casters\DataTransferObjectCaster;
use Tempest\Reflection\FunctionReflector;
use Tempest\Reflection\PropertyReflector;
use Tempest\Reflection\TypeReflector;

#[Singleton]
final class CasterFactory
{
    /**
     * @var array<string, array{string|Closure, class-string<\Tempest\Mapper\Caster>|Closure, int}[]>
     */
    private array $casters = [];

    public function __construct(
        private readonly Container $container,
    ) {}

    /**
     * @param class-string<\Tempest\Mapper\Caster> $casterClass
     */
    public function addCaster(string|array|Closure $for, string|Closure $casterClass, string $context = Context::DEFAULT, int $priority = 0): self
    {
        $this->casters[$context] ??= [];
        $this->casters[$context][] = [$for, $casterClass, $priority];

        usort($this->casters[$context], static fn (array $a, array $b) => $a[2] <=> $b[2]);

        return $this;
    }

    public function forProperty(PropertyReflector $property, string $context = Context::DEFAULT): ?Caster
    {
        $type = $property->getType();
        $castWith = $property->getAttribute(CastWith::class);

        if ($castWith === null && $type->isClass()) {
            $castWith = $type->asClass()->getAttribute(CastWith::class, recursive: true);

            if ($castWith === null && $type->asClass()->getAttribute(SerializeAs::class)) {
                return $this->container->get(DataTransferObjectCaster::class);
            }
        }

        if ($castWith) {
            return $this->container->get($castWith->className);
        }

        if ($casterAttribute = $property->getAttribute(ProvidesCaster::class)) {
            return $this->container->get($casterAttribute->caster);
        }

        $casters = [
            ...($this->casters[$context] ?? []),
            ...($this->casters[Context::DEFAULT] ?? []),
        ];

        foreach ($casters as [$for, $casterClass]) {
            if (! $this->casterMatches($for, $property)) {
                continue;
            }

            if (is_a($casterClass, DynamicCaster::class, allow_string: true)) {
                return $casterClass::make($property);
            }

            return $this->container->get($casterClass);
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
}
