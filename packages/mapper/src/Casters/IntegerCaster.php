<?php

declare(strict_types=1);

namespace Tempest\Mapper\Casters;

use Tempest\Core\Priority;
use Tempest\Mapper\Caster;
use Tempest\Mapper\DynamicCaster;
use Tempest\Reflection\PropertyReflector;
use Tempest\Reflection\TypeReflector;

#[Priority(Priority::NORMAL)]
final readonly class IntegerCaster implements Caster, DynamicCaster
{
    public static function accepts(PropertyReflector|TypeReflector $input): bool
    {
        $type = $input instanceof PropertyReflector
            ? $input->getType()
            : $input;

        return in_array($type->getName(), ['int', 'integer'], strict: true);
    }

    public function cast(mixed $input): int
    {
        return intval($input);
    }
}
