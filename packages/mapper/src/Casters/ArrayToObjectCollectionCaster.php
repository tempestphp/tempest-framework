<?php

declare(strict_types=1);

namespace Tempest\Mapper\Casters;

use Tempest\Mapper\Caster;
use Tempest\Mapper\Mappers\ObjectToArrayMapper;
use Tempest\Reflection\PropertyReflector;

final readonly class ArrayToObjectCollectionCaster implements Caster
{
    public function __construct(
        private PropertyReflector $property,
    ) {}

    public function cast(mixed $input): mixed
    {
        $values = [];
        $iterableType = $this->property->getIterableType();
        $objectCaster = new ObjectCaster($iterableType);

        foreach ($input as $key => $item) {
            if (is_object($item) && $iterableType->matches($item::class)) {
                $values[$key] = $item;
            } else {
                $values[$key] = $objectCaster->cast($item);
            }
        }

        return $values;
    }
}
