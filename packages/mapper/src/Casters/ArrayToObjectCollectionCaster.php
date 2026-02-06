<?php

declare(strict_types=1);

namespace Tempest\Mapper\Casters;

use Tempest\Core\Priority;
use Tempest\Mapper\Caster;
use Tempest\Mapper\ConfigurableCaster;
use Tempest\Mapper\Context;
use Tempest\Mapper\DynamicCaster;
use Tempest\Reflection\PropertyReflector;
use Tempest\Reflection\TypeReflector;
use Tempest\Support\Json;

#[Priority(Priority::HIGHEST)]
final readonly class ArrayToObjectCollectionCaster implements Caster, DynamicCaster, ConfigurableCaster
{
    public function __construct(
        private PropertyReflector $property,
    ) {}

    public static function accepts(PropertyReflector|TypeReflector $input): bool
    {
        if ($input instanceof TypeReflector) {
            return false;
        }

        return $input->getIterableType() !== null;
    }

    public static function configure(PropertyReflector $property, Context $context): self
    {
        return new self($property);
    }

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
