<?php

declare(strict_types=1);

namespace Tempest\Mapper;

use Closure;
use Tempest\Container\Container;
use Tempest\Container\Singleton;
use Tempest\Reflection\PropertyReflector;
use Tempest\Support\Memoization\HasMemoization;
use UnitEnum;

#[Singleton]
final class CasterFactory
{
    use HasMemoization;

    /**
     * @var array<string, array{class-string<\Tempest\Mapper\Caster>, int}[]>
     */
    private(set) array $casters = [];

    private(set) Context|UnitEnum|string|null $context = null;

    public function __construct(
        private readonly Container $container,
    ) {}

    /**
     * @param class-string<\Tempest\Mapper\Caster> $casterClass
     */
    public function addCaster(string $casterClass, int $priority = 0, Context|UnitEnum|string|null $context = null): self
    {
        $context = MappingContext::from($context);

        $this->casters[$context->name] ??= [];
        $this->casters[$context->name][] = [$casterClass, $priority];

        usort($this->casters[$context->name], static fn (array $a, array $b) => $a[1] <=> $b[1]);

        return $this;
    }

    /**
     * Sets the context that should be passed to casters.
     */
    public function in(Context|UnitEnum|string $context): self
    {
        $caster = clone $this;
        $caster->context = $context;

        return $caster;
    }

    public function forProperty(PropertyReflector $property): ?Caster
    {
        $context = MappingContext::from($this->context);

        return $this->memoize('[' . $context->name . '] ' . $property->getName(), function () use ($property, $context) {
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

            foreach ($this->resolveCasters() as [$casterClass]) {
                if (is_a($casterClass, DynamicCaster::class, allow_string: true)) {
                    if (! $casterClass::accepts($property)) {
                        continue;
                    }
                }

                if (is_a($casterClass, ConfigurableCaster::class, allow_string: true)) {
                    return $casterClass::configure($property, $context);
                }

                return $this->container->get($casterClass, context: $context);
            }

            return null;
        });
    }

    /**
     * @return array{class-string<\Tempest\Mapper\Caster>|Closure,int}[]
     */
    private function resolveCasters(): array
    {
        return [
            ...($this->casters[MappingContext::from($this->context)->name] ?? []),
            ...($this->casters[MappingContext::default()->name] ?? []),
        ];
    }
}
