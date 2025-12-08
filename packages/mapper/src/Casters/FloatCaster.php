<?php

declare(strict_types=1);

namespace Tempest\Mapper\Casters;

use Tempest\Core\Priority;
use Tempest\Mapper\Caster;
use Tempest\Mapper\Context;

#[Context(Context::DEFAULT)]
#[Priority(Priority::NORMAL)]
final readonly class FloatCaster implements Caster
{
    public static function for(): array
    {
        return ['float', 'double'];
    }

    public function cast(mixed $input): float
    {
        return floatval($input);
    }
}
