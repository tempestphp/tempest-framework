<?php

declare(strict_types=1);

namespace Tempest\ORM\Casters;

use Tempest\ORM\Caster;

final readonly class IntegerCaster implements Caster
{
    public function cast(mixed $input): int
    {
        return intval($input);
    }
}
