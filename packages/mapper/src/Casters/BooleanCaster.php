<?php

declare(strict_types=1);

namespace Tempest\Mapper\Casters;

use Tempest\Mapper\Caster;

final readonly class BooleanCaster implements Caster
{
    public function cast(mixed $input): bool
    {
        if (is_string($input)) {
            $input = mb_strtolower($input);
        }

        return match ($input) {
            1, '1', true, 'true', 'enabled', 'on', 'yes' => true,
            default => false,
        };
    }
}
