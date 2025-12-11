<?php

declare(strict_types=1);

namespace Tempest\Mapper\Casters;

use Tempest\Core\Priority;
use Tempest\Mapper\Caster;
use Tempest\Mapper\DynamicCaster;
use Tempest\Reflection\PropertyReflector;
use Tempest\Reflection\TypeReflector;
use Tempest\Support\Json;

#[Priority(Priority::NORMAL)]
final class JsonToArrayCaster implements Caster, DynamicCaster
{
    public static function accepts(PropertyReflector|TypeReflector $input): bool
    {
        $type = $input instanceof PropertyReflector
            ? $input->getType()
            : $input;

        return $type->getName() === 'array';
    }

    public function cast(mixed $input): array
    {
        if (is_array($input)) {
            return $input;
        }

        return Json\decode($input);
    }
}
