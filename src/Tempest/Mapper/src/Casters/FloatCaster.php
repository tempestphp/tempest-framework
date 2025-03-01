<?php

declare(strict_types=1);

namespace Tempest\Mapper\Casters;

use Tempest\Mapper\Caster;

final readonly class FloatCaster implements Caster
{
    public function cast(mixed $input): float
    {
        return floatval($input);
    }

    public function serialize(mixed $input): string
    {
        return (string) $input;
    }
}
