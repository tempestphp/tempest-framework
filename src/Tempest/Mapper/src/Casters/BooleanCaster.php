<?php

declare(strict_types=1);

namespace Tempest\Mapper\Casters;

use Tempest\Mapper\Caster;
use Tempest\Mapper\Exceptions\CannotSerializeValue;

final readonly class BooleanCaster implements Caster
{
    public function cast(mixed $input): bool
    {
        return boolval($input);
    }

    public function serialize(mixed $input): string
    {
        if (! is_bool($input)) {
            throw new CannotSerializeValue('boolean');
        }

        return $input ? 'true' : 'false';
    }
}
