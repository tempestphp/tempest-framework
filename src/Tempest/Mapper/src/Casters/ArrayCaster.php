<?php

declare(strict_types=1);

namespace Tempest\Mapper\Casters;

use Tempest\Mapper\Caster;
use Tempest\Mapper\Exceptions\CannotSerializeValue;

final class ArrayCaster implements Caster
{
    public function cast(mixed $input): array
    {
        if (is_array($input)) {
            return $input;
        }

        return json_decode($input, associative: true);
    }

    public function serialize(mixed $input): string
    {
        if (! is_array($input)) {
            throw new CannotSerializeValue('array');
        }

        return json_encode($input);
    }
}
