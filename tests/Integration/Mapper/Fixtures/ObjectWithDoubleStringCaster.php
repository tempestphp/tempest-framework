<?php

declare(strict_types=1);

<<<<<<<< HEAD:tests/Integration/Mapper/Fixtures/ObjectWithDoubleStringCaster.php
namespace Tests\Tempest\Integration\Mapper\Fixtures;
========
namespace Tempest\Mapper\Tests\Fixtures;
>>>>>>>> main:src/Tempest/Mapper/tests/Fixtures/ObjectWithDoubleStringCaster.php

use Tempest\Mapper\CastWith;

final class ObjectWithDoubleStringCaster
{
    #[CastWith(DoubleStringCaster::class)]
    public string $prop;
}
