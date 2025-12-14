<?php

declare(strict_types=1);

namespace Tempest\Mapper\Casters;

use Tempest\Core\Priority;
use Tempest\Mapper\Caster;
use Tempest\Mapper\ConfigurableCaster;
use Tempest\Mapper\Context;
use Tempest\Mapper\DynamicCaster;
use Tempest\Mapper\Mappers\ArrayToObjectMapper;
use Tempest\Reflection\PropertyReflector;
use Tempest\Reflection\TypeReflector;

use function Tempest\Mapper\map;

#[Priority(Priority::HIGH)]
final readonly class ObjectCaster implements Caster, DynamicCaster, ConfigurableCaster
{
    public function __construct(
        private TypeReflector $type,
    ) {}

    public static function accepts(PropertyReflector|TypeReflector $input): bool
    {
        $type = $input instanceof PropertyReflector
            ? $input->getType()
            : $input;

        return $type->isClass();
    }

    public static function configure(PropertyReflector $property, Context $context): static
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
