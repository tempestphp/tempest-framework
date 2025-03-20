<?php

declare(strict_types=1);

namespace Tempest\Mapper;

use Closure;
use Tempest\Reflection\PropertyReflector;

use function Tempest\get;

final class CasterFactory
{
    /**
     * @var array{string|Closure, class-string<\Tempest\Mapper\Caster>|Closure}[]
     */
    private array $casters = [];

    /**
     * @param class-string<\Tempest\Mapper\Caster> $casterClass
     */
    public function addCaster(string|Closure $for, string|Closure $casterClass): self
    {
        $this->casters = [[$for, $casterClass], ...$this->casters];

        return $this;
    }

    public function forProperty(PropertyReflector $property): ?Caster
    {
        $type = $property->getType();

        // Get CastWith from the property
        $castWith = $property->getAttribute(CastWith::class);

        // Get CastWith from the property's type if there's no property-defined CastWith
        if ($castWith === null && $type->isClass()) {
            $castWith = $type->asClass()->getAttribute(CastWith::class, recursive: true);
        }

        // Return the caster if defined with CastWith
        if ($castWith !== null) {
            // Resolve the caster from the container
            return get($castWith->className);
        }

        // Resolve caster from manual additions
        foreach ($this->casters as [$for, $casterClass]) {
            if (is_callable($for) && $for($property) || is_string($for) && $type->matches($for) || $type->getName() === $for) {
                return is_callable($casterClass)
                    ? $casterClass($property)
                    : get($casterClass);
            }
        }

        return null;
    }
}
