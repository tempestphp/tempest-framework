<?php

declare(strict_types=1);

namespace Tempest\Mapper\Casters;

use BackedEnum;
use Tempest\Mapper\Caster;
use Tempest\Mapper\ConfigurableCaster;
use Tempest\Mapper\Context;
use Tempest\Mapper\DynamicCaster;
use Tempest\Reflection\PropertyReflector;
use Tempest\Reflection\TypeReflector;
use Tempest\Support\Priority;
use UnitEnum;

#[Priority(Priority::HIGHEST)]
final readonly class EnumCaster implements Caster, DynamicCaster, ConfigurableCaster
{
    /**
     * @param class-string<UnitEnum> $enum
     */
    public function __construct(
        private string $enum,
        private bool $nullable = false,
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
        return new self(
            enum: $property->getType()->getName(),
            nullable: $property->isNullable(),
        );
    }

    public function cast(mixed $input): ?object
    {
        if (is_string($input)) {
            $input = trim($input);
        }

        if ($this->nullable && ($input === null || $input === '' || is_string($input) && mb_strtolower($input) === 'null')) {
            return null;
        }

        if ($input === null) {
            return null;
        }

        if (is_a($input, $this->enum)) {
            return $input;
        }

        if (defined("{$this->enum}::{$input}")) {
            return constant("{$this->enum}::{$input}");
        }

        if (! is_a($this->enum, BackedEnum::class, allow_string: true)) {
            return null;
        }

        return forward_static_call("{$this->enum}::from", $input);
    }
}
