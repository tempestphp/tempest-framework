<?php

declare(strict_types=1);

namespace Tempest\Mapper\Casters;

use Tempest\Core\Priority;
use Tempest\Mapper\Caster;
use Tempest\Mapper\Context;
use Tempest\Mapper\DynamicCaster;
use Tempest\Reflection\PropertyReflector;
use UnitEnum;

#[Context(Context::DEFAULT)]
#[Priority(Priority::HIGHEST)]
final readonly class EnumCaster implements Caster, DynamicCaster
{
    /**
     * @param class-string<UnitEnum> $enum
     */
    public function __construct(
        private string $enum,
    ) {}

    public static function make(PropertyReflector $property): Caster
    {
        return new self(enum: $property->getType()->getName());
    }

    public static function for(): string
    {
        return UnitEnum::class;
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
