<?php

declare(strict_types=1);

namespace Tempest\Mapper\Casters;

use Tempest\Mapper\Caster;

class MixedCaster implements Caster
{
    public function cast(mixed $input): mixed
    {
        return $input;
    }
}
