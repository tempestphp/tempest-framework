<?php

declare(strict_types=1);

namespace Tempest\Mapper\Attributes;

use Attribute;
use BackedEnum;
use Tempest\Mapper\Context as MapperContext;
use UnitEnum;

/**
 * Defines the context in which a mapper, serializer, or caster operates.
 */
#[Attribute(Attribute::TARGET_CLASS)]
final class Context implements MapperContext
{
    public string $key {
        get {
            if ($this->name instanceof BackedEnum) {
                return $this->name->value;
            }

            if ($this->name instanceof UnitEnum) {
                return $this->name->name;
            }

            return $this->name;
        }
    }

    public function __construct(
        public BackedEnum|UnitEnum|string $name,
    ) {}
}
