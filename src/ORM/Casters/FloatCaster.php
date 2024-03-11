<?php

declare(strict_types=1);

namespace Tempest\ORM\Casters;

use Tempest\ORM\Caster;

final readonly class FloatCaster implements Caster
{
    public function cast(mixed $input): float
    {
        return floatval($input);
    }
}
