<?php

declare(strict_types=1);

namespace Tempest\Mapper;

use BackedEnum;
use DateTime as NativeDateTime;
use DateTimeImmutable as NativeDateTimeImmutable;
use DateTimeInterface as NativeDateTimeInterface;
use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Container\Singleton;
use Tempest\DateTime\DateTime;
use Tempest\DateTime\DateTimeInterface;
use Tempest\Mapper\Casters\ArrayToObjectCollectionCaster;
use Tempest\Mapper\Casters\DateTimeCaster;
use Tempest\Mapper\Casters\EnumCaster;
use Tempest\Mapper\Casters\JsonToArrayCaster;
use Tempest\Mapper\Casters\NativeDateTimeCaster;
use Tempest\Mapper\Casters\ObjectCaster;
use Tempest\Reflection\PropertyReflector;

final class CasterFactoryInitializer implements Initializer
{
    #[Singleton]
    public function initialize(Container $container): CasterFactory
    {
        return new CasterFactory()
            ->addCaster('array', JsonToArrayCaster::class)
            ->addCaster(fn (PropertyReflector $property) => $property->getIterableType() !== null, fn (PropertyReflector $property) => new ArrayToObjectCollectionCaster($property))
            ->addCaster(fn (PropertyReflector $property) => $property->getType()->isClass(), fn (PropertyReflector $property) => new ObjectCaster($property->getType()))
            ->addCaster(BackedEnum::class, fn (PropertyReflector $property) => new EnumCaster($property->getType()->getName()))
            ->addCaster(DateTimeInterface::class, DateTimeCaster::fromProperty(...))
            ->addCaster(NativeDateTimeImmutable::class, NativeDateTimeCaster::fromProperty(...))
            ->addCaster(NativeDateTime::class, NativeDateTimeCaster::fromProperty(...))
            ->addCaster(NativeDateTimeInterface::class, NativeDateTimeCaster::fromProperty(...))
            ->addCaster(DateTime::class, DateTimeCaster::fromProperty(...));
    }
}
