<?php

declare(strict_types=1);

namespace Tempest\Mapper\Casters;

use Tempest\Mapper\Caster;
use Tempest\Reflection\PropertyReflector;

use function Tempest\map;

class UnionCaster implements Caster
{
    public function __construct(
        private PropertyReflector $property,
    ) {}

    public function cast(mixed $input): mixed
    {
        $propertyType = $this->property->getDocType() ?? $this->property->getType();

        // for native types that already match, return early
        foreach ($propertyType->split() as $type) {
            if ($type->accepts($input)) {
                return $input;
            }
        }

        $lastException = null;

        // as last resort, try to map to any of the union types
        foreach ($propertyType->split() as $type) {
            try {
                return map($input)->to($type->getName());
            } catch (\Throwable $e) {
                $lastException = $e;
            }
        }

        throw $lastException;
    }
}
