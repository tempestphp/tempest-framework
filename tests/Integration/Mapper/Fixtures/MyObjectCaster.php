<?php

declare(strict_types=1);

<<<<<<<< HEAD:tests/Integration/Mapper/Fixtures/MyObjectCaster.php
namespace Tests\Tempest\Integration\Mapper\Fixtures;
========
namespace Tempest\Mapper\Tests\Fixtures;
>>>>>>>> main:src/Tempest/Mapper/tests/Fixtures/MyObjectCaster.php

use Tempest\Mapper\Caster;

final class MyObjectCaster implements Caster
{
    public function cast(mixed $input): MyObject
    {
        return new MyObject($input);
    }
}
