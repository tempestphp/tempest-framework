<?php

declare(strict_types=1);

namespace Tempest\Mapper\Casters;

use Tempest\Core\Priority;
use Tempest\Mapper\Caster;
use Tempest\Mapper\Context;

#[Context(Context::DEFAULT)]
#[Priority(Priority::NORMAL)]
final readonly class IntegerCaster implements Caster
{
    public static function for(): array
    {
        return ['int', 'integer']; // TODO: not sure if both aliases are needed
    }

    public function cast(mixed $input): int
    {
        return intval($input);
    }
}
