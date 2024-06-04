<?php

declare(strict_types=1);

namespace Tempest\Mapper\Casters;

use Tempest\Mapper\Caster;

final readonly class BooleanCaster implements Caster
{
    public function cast(mixed $input): bool
    {
        return boolval($input);
    }
}
