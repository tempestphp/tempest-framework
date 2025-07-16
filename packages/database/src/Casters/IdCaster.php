<?php

declare(strict_types=1);

namespace Tempest\Database\Casters;

use Tempest\Database\Id;
use Tempest\Mapper\Caster;

final readonly class IdCaster implements Caster
{
    public function cast(mixed $input): Id
    {
        if ($input instanceof Id) {
            return $input;
        }

        return new Id($input);
    }
}
