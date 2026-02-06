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
use UnitEnum;

#[Priority(Priority::HIGHEST)]
final readonly class EnumCaster implements Caster, DynamicCaster, ConfigurableCaster
{
    /**
     * @param class-string<UnitEnum> $enum
     */
    public function __construct(
        private string $enum,
    ) {}

    public static function accepts(PropertyReflector|TypeReflector $input): bool
    {
        $type = $input instanceof PropertyReflector
            ? $input->getType()
            : $input;

        return $type->matches(UnitEnum::class);
    }

    public static function configure(PropertyReflector $property, Context $context): self
    {
        return new self(enum: $property->getType()->getName());
    }

    public function cast(mixed $input): ?object
    {
        if ($input === null) {
            return null;
        }

        if (is_a($input, $this->enum)) {
            return $input;
        }

        if (defined("{$this->enum}::{$input}")) {
            return constant("{$this->enum}::{$input}");
        }

        if (! is_a($this->enum, \BackedEnum::class, allow_string: true)) {
            return null;
        }

        return forward_static_call("{$this->enum}::from", $input);
    }
}
