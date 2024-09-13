<?php

declare(strict_types=1);

namespace Tempest\Mapper\Tests\Fixtures;

use Tempest\Mapper\Caster;

final class MyObjectCaster implements Caster
{
    public function cast(mixed $input): MyObject
    {
        return new MyObject($input);
    }
}
