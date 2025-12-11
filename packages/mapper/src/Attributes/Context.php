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
    public string $name {
        get {
            if ($this->context instanceof BackedEnum) {
                return $this->context->value;
            }

            if ($this->context instanceof UnitEnum) {
                return $this->context->name;
            }

            return $this->context;
        }
    }

    public function __construct(
        public BackedEnum|UnitEnum|string $context,
    ) {}
}
