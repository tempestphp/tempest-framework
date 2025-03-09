<?php

namespace Tempest\Mapper\Casters;

use Tempest\Mapper\Caster;
use Tempest\Reflection\PropertyReflector;

final readonly class ArrayObjectCaster implements Caster
{
    public function __construct(
        private PropertyReflector $property
    ) {}

    public function cast(mixed $input): mixed
    {
        $values = [];

        $objectCaster = new ObjectCaster($this->property->getIterableType());

        foreach ($input as $key => $item) {
            $values[$key] = $objectCaster->cast($item);
        }

        return $values;
    }

    public function serialize(mixed $input): string
    {
        // TODO
    }
}