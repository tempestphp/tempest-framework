<?php

declare(strict_types=1);

namespace Tempest\Mapper\Casters;

use Tempest\Mapper\Caster;
use Tempest\Mapper\ConfigurableCaster;
use Tempest\Mapper\Context;
use Tempest\Mapper\DynamicCaster;
use Tempest\Reflection\PropertyReflector;
use Tempest\Reflection\TypeReflector;
use Tempest\Support\Priority;

#[Priority(Priority::NORMAL)]
final readonly class BooleanCaster implements Caster, DynamicCaster, ConfigurableCaster
{
    public function __construct(
        private bool $nullable = false,
    ) {}

    public static function accepts(PropertyReflector|TypeReflector $input): bool
    {
        $type = $input instanceof PropertyReflector
            ? $input->getType()
            : $input;

        return in_array($type->getName(), ['bool', 'boolean'], strict: true);
    }

    public static function configure(PropertyReflector $property, Context $context): self
    {
        return new self(nullable: $property->isNullable());
    }

    public function cast(mixed $input): ?bool
    {
        if (is_string($input)) {
            $input = mb_strtolower(trim($input));
        }

        if ($this->nullable && in_array($input, [null, '', 'null'], true)) {
            return null;
        }

        return match ($input) {
            1, '1', true, 'true', 'enabled', 'on', 'yes' => true,
            default => false,
        };
    }
}
