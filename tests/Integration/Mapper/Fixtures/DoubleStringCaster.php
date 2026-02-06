<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Mapper\Fixtures;

use Tempest\Mapper\Caster;

final class DoubleStringCaster implements Caster
{
    public static function for(): string
    {
        return 'string';
    }

    public function cast(mixed $input): string
    {
        return $input . $input;
    }

    public function serialize(mixed $input): string
    {
        return $input;
    }
}
