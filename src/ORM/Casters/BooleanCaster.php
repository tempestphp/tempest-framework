<?php

declare(strict_types=1);

namespace Tempest\ORM\Casters;

use Tempest\ORM\Caster;

final readonly class BooleanCaster implements Caster
{
    public function cast(mixed $input): bool
    {
        return boolval($input);
    }
}
