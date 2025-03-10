<?php

declare(strict_types=1);

namespace Tempest\Mapper\Casters;

use Tempest\Mapper\Caster;
use Tempest\Mapper\Mappers\ObjectToArrayMapper;
use Tempest\Reflection\PropertyReflector;

use function Tempest\map;

final readonly class ArrayObjectCaster implements Caster
{
    public function __construct(
        private PropertyReflector $property,
    ) {
    }

    public function cast(mixed $input): mixed
    {
        $values = [];

        $objectCaster = new ObjectCaster($this->property->getIterableType());

        foreach ($input as $key => $item) {
            $values[$key] = $objectCaster->cast($item);
        }

        return $values;
    }

    public function serialize(mixed $input): array
    {
        $values = [];

        foreach ($input as $key => $item) {
            $values[$key] = map($item)->with(ObjectToArrayMapper::class)->do();
        }

        return $values;
    }
}
