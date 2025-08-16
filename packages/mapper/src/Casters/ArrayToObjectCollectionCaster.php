<?php

declare(strict_types=1);

namespace Tempest\Mapper\Casters;

use Tempest\Mapper\Caster;
use Tempest\Reflection\PropertyReflector;
use Tempest\Support\Json;

final readonly class ArrayToObjectCollectionCaster implements Caster
{
    public function __construct(
        private PropertyReflector $property,
    ) {}

    public function cast(mixed $input): mixed
    {
        $values = [];
        $iterableType = $this->property->getIterableType();

        $caster = $iterableType->isEnum()
            ? new EnumCaster($iterableType->getName())
            : new ObjectCaster($iterableType);

        if (Json\is_valid($input)) {
            $input = Json\decode($input);
        }

        foreach ($input as $key => $item) {
            if (is_object($item) && $iterableType->matches($item::class)) {
                $values[$key] = $item;
            } else {
                $values[$key] = $caster->cast($item);
            }
        }

        return $values;
    }
}
