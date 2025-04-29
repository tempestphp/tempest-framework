<?php

declare(strict_types=1);

namespace Tempest\Mapper\Casters;

use Tempest\Mapper\Caster;

final class JsonToArrayCaster implements Caster
{
    public function cast(mixed $input): array
    {
        if (is_array($input)) {
            return $input;
        }

        return json_decode($input, associative: true);
    }
}
