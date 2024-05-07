<?php

declare(strict_types=1);

namespace Tests\Tempest\Unit\Mapper\Fixtures;

use Tempest\Mapper\Caster;

final readonly class DoubleStringCaster implements Caster
{
    public function cast(mixed $input): string
    {
        return $input . $input;
    }
}
