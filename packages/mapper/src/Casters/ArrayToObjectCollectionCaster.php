<?php

declare(strict_types=1);

namespace Tempest\Mapper\Casters;

use Tempest\Mapper\Caster;
use Tempest\Mapper\ConfigurableCaster;
use Tempest\Mapper\Context;
use Tempest\Mapper\DynamicCaster;
use Tempest\Reflection\PropertyReflector;
use Tempest\Reflection\TypeReflector;
use Tempest\Support\Json;
use Tempest\Support\Priority;

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

        return $input->getIterableType() instanceof TypeReflector;
    }

    public static function configure(PropertyReflector $property, Context $context): self
    {
        return new self($property);
    }

    public function cast(mixed $input): mixed
    {
        $iterableType = $this->property->getIterableType();

        if (Json\is_valid($input)) {
            $input = Json\decode($input);
        }

        if ($iterableType->isBuiltIn()) {
            return $input;
        }

        $caster = $iterableType->isEnum()
            ? new EnumCaster($iterableType->getName())
            : new ObjectCaster($iterableType);

        $values = [];

        foreach ($input as $key => $item) {
            $values[$key] = is_object($item) && $iterableType->matches($item::class) ? $item : $caster->cast($item);
        }

        return $values;
    }
}
