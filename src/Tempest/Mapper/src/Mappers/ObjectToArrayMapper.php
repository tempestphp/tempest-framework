<?php

declare(strict_types=1);

namespace Tempest\Mapper\Mappers;

use JsonSerializable;
use Tempest\Mapper\Mapper;
use Tempest\Mapper\MapTo;
use Tempest\Mapper\SerializerFactory;
use Tempest\Reflection\ClassReflector;

final readonly class ObjectToArrayMapper implements Mapper
{
    public function __construct(
        private SerializerFactory $serializerFactory,
    ) {}

    public function canMap(mixed $from, mixed $to): bool
    {
        return $to === MapTo::ARRAY && is_object($from);
    }

    public function map(mixed $from, mixed $to): array
    {
        if ($from instanceof JsonSerializable) {
            return $from->jsonSerialize();
        }

        $class = new ClassReflector($from);

        $data = [];

        foreach ($class->getPublicProperties() as $property)
        {
            $serializer = $this->serializerFactory->forProperty($property);

            if ($serializer !== null) {
                $data[$property->getName()] = $serializer->serialize($property->getValue($from));

                continue;
            }

            $data[$property->getName()] = $property->getValue($from);
        }

        return $data;
    }
}
