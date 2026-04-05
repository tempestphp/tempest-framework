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

#[Priority(Priority::NORMAL)]
final readonly class FloatCaster implements Caster, DynamicCaster, ConfigurableCaster
{
    public function __construct(
        private bool $nullable = false,
    ) {}

    public static function accepts(PropertyReflector|TypeReflector $input): bool
    {
        $type = $input instanceof PropertyReflector
            ? $input->getType()
            : $input;

        return in_array($type->getName(), ['float', 'double'], strict: true);
    }

    public static function configure(PropertyReflector $property, Context $context): self
    {
        return new self(nullable: $property->isNullable());
    }

    public function cast(mixed $input): ?float
    {
        if (is_string($input)) {
            $input = mb_strtolower(trim($input));
        }

        if ($this->nullable && ($input === null || $input === '' || $input === 'null')) {
            return null;
        }

        return floatval($input);
    }
}
