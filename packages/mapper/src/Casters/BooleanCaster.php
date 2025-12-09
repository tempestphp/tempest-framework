<?php

declare(strict_types=1);

namespace Tempest\Mapper\Casters;

use Tempest\Core\Priority;
use Tempest\Mapper\Caster;

#[Priority(Priority::NORMAL)]
final readonly class BooleanCaster implements Caster
{
    public static function for(): array
    {
        return ['bool', 'boolean']; // TODO: not sure if both aliases are needed
    }

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
