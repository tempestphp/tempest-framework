<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Mapper\Fixtures;

use Tempest\Mapper\Caster;

final class MyObjectCaster implements Caster
{
    public function cast(mixed $input): MyObject
    {
        return new MyObject($input);
    }

    public function serialize(mixed $input): string
    {
        return serialize($input);
    }
}
