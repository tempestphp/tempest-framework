<?php

namespace Tempest\Mapper;

use Stringable;
use UnitEnum;

final class MappingContext implements Context, Stringable
{
    public function __construct(
        private(set) string $key,
    ) {}

    public static function default(): Context
    {
        return new self('default');
    }

    public static function from(Context|UnitEnum|string|null $context): Context
    {
        if (! $context) {
            return self::default();
        }

        if ($context instanceof Context) {
            return $context;
        }

        if ($context instanceof UnitEnum) {
            $context = $context->name;
        }

        return new self($context);
    }

    public function __toString(): string
    {
        return $this->key;
    }
}
