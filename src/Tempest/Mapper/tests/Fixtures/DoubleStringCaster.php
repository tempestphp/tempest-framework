<?php

declare(strict_types=1);

namespace Tempest\Mapper\Tests\Fixtures;

use Tempest\Mapper\Caster;

final class DoubleStringCaster implements Caster
{
    public function cast(mixed $input): string
    {
        return $input . $input;
    }
}
