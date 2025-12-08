<?php

namespace Tests\Tempest\Integration\Mapper\Fixtures;

use Closure;
use Tempest\Mapper\Serializer;

final class DoubleStringSerializer implements Serializer
{
    public static function for(): string|Closure
    {
        return 'string';
    }

    public function serialize(mixed $input): string
    {
        return $input . $input;
    }
}
