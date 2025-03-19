<?php

declare(strict_types=1);

namespace Tempest\Mapper\Casters;

use Tempest\Mapper\Caster;

final readonly class IntegerCaster implements Caster
{
    public function cast(mixed $input): int
    {
        return intval($input);
    }
}
