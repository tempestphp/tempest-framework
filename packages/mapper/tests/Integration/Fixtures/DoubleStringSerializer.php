<?php

namespace Tempest\Mapper\Tests\Integration\Fixtures;

use Tempest\Mapper\Serializer;

final class DoubleStringSerializer implements Serializer
{
    public function serialize(mixed $input): string
    {
        return $input . $input;
    }
}
