<?php

namespace Tempest\ORM\Casters;

use Tempest\Database\Id;
use Tempest\Interfaces\Caster;

final readonly class IdCaster implements Caster
{
    public function cast(mixed $input): Id
    {
        return new Id($input);
    }
}