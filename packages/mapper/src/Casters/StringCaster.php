<?php

declare(strict_types=1);

namespace Tempest\Mapper\Casters;

use Tempest\Mapper\Caster;

class StringCaster implements Caster
{
    public function cast(mixed $input): string
    {
        return (string) $input;
    }
}
