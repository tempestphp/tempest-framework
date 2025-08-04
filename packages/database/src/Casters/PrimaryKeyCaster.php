<?php

declare(strict_types=1);

namespace Tempest\Database\Casters;

use Tempest\Database\PrimaryKey;
use Tempest\Mapper\Caster;

final readonly class PrimaryKeyCaster implements Caster
{
    public function cast(mixed $input): PrimaryKey
    {
        if ($input instanceof PrimaryKey) {
            return $input;
        }

        return new PrimaryKey($input);
    }
}
