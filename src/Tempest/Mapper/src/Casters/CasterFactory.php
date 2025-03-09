<?php

declare(strict_types=1);

namespace Tempest\Mapper\Casters;

use BackedEnum;
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use ReflectionException;
use Tempest\Mapper\Caster;
use Tempest\Mapper\CastWith;
use Tempest\Reflection\PropertyReflector;
use function Tempest\get;

final readonly class CasterFactory
{
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

        // If the type is an enum, we'll use the enum caster
        if ($type->matches(BackedEnum::class)) {
            return new EnumCaster($type->getName());
        }

        // If the property has an iterable type, we'll cast it with the array object caster
        if ($property->getIterableType() !== null) {
            return new ArrayObjectCaster($property);
        }

        // Try a built-in caster
        $builtInCaster = match ($type->getName()) {
            DateTimeImmutable::class, DateTimeInterface::class, DateTime::class => DateTimeCaster::fromProperty($property),
            'array' => new ArrayJsonCaster(),
            default => null,
        };

        if ($builtInCaster !== null) {
            return $builtInCaster;
        }

        // If the type's a class, we'll cast it with the generic object caster
        if ($type->isClass()) {
            return new ObjectCaster($type);
        }

        return null;
    }
}
