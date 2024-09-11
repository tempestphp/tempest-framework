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
        // Get CastWith from the property
        $castWith = $property->getAttribute(CastWith::class);

        $type = $property->getType();

        // Get CastWith from the property's type
        if ($castWith === null) {
            try {
                $castWith = $type->asClass()->getAttribute(CastWith::class);
            } catch (ReflectionException) {
                // Could not resolve CastWith from the type
            }
        }

        if ($castWith !== null) {
            // Resolve the caster from the container
            return get($castWith->className);
        }

        // Check if backed enum
        if ($type->matches(BackedEnum::class)) {
            return new EnumCaster($type->getName());
        }

        // Get Caster from built-in casters
        return match ($type->getName()) {
            'int' => new IntegerCaster(),
            'float' => new FloatCaster(),
            'bool' => new BooleanCaster(),
            DateTimeImmutable::class, DateTimeInterface::class, DateTime::class => DateTimeCaster::fromProperty($property),
            default => null,
        };
    }
}
