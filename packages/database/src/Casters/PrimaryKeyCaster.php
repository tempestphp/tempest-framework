<?php

declare(strict_types=1);

namespace Tempest\Database\Casters;

use Tempest\Core\Priority;
use Tempest\Database\PrimaryKey;
use Tempest\Mapper\Caster;
use Tempest\Mapper\DynamicCaster;
use Tempest\Reflection\PropertyReflector;
use Tempest\Reflection\TypeReflector;

#[Priority(Priority::HIGHEST)]
final readonly class PrimaryKeyCaster implements Caster, DynamicCaster
{
    public static function accepts(PropertyReflector|TypeReflector $input): bool
    {
        $type = $input instanceof PropertyReflector
            ? $input->getType()
            : $input;

        return $type->matches(PrimaryKey::class);
    }

    public function cast(mixed $input): PrimaryKey
    {
        if ($input instanceof PrimaryKey) {
            return $input;
        }

        return new PrimaryKey($input);
    }
}
