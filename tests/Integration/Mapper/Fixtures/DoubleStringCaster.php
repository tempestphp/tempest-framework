<?php

declare(strict_types=1);

<<<<<<<< HEAD:tests/Integration/Mapper/Fixtures/DoubleStringCaster.php
namespace Tests\Tempest\Integration\Mapper\Fixtures;
========
namespace Tempest\Mapper\Tests\Fixtures;
>>>>>>>> main:src/Tempest/Mapper/tests/Fixtures/DoubleStringCaster.php

use Tempest\Mapper\Caster;

final class DoubleStringCaster implements Caster
{
    public function cast(mixed $input): string
    {
        return $input . $input;
    }
}
