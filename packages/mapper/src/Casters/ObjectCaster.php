<?php

declare(strict_types=1);

namespace Tempest\Mapper\Casters;

use Closure;
use Tempest\Core\Priority;
use Tempest\Mapper\Caster;
use Tempest\Mapper\Context;
use Tempest\Mapper\DynamicCaster;
use Tempest\Mapper\Mappers\ArrayToObjectMapper;
use Tempest\Reflection\PropertyReflector;
use Tempest\Reflection\TypeReflector;

use function Tempest\Mapper\map;

#[Priority(Priority::HIGH)]
final readonly class ObjectCaster implements Caster, DynamicCaster
{
    public function __construct(
        private TypeReflector $type,
    ) {}

    public static function for(): Closure
    {
        return fn (PropertyReflector $property) => $property->getType()->isClass();
    }

    public static function make(PropertyReflector $property, Context $context): static
    {
        return new self($property->getType());
    }

    public function cast(mixed $input): mixed
    {
        // TODO: difference with ArrayToObjectCaster? This can probably be removed after we've added support for #984
        return map($input)
            ->with(ArrayToObjectMapper::class)
            ->to($this->type->getName());
    }
}
