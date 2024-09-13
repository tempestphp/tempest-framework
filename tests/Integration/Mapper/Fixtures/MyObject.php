<?php

declare(strict_types=1);

<<<<<<<< HEAD:tests/Integration/Mapper/Fixtures/MyObject.php
namespace Tests\Tempest\Integration\Mapper\Fixtures;
========
namespace Tempest\Mapper\Tests\Fixtures;
>>>>>>>> main:src/Tempest/Mapper/tests/Fixtures/MyObject.php

use Tempest\Mapper\CastWith;

#[CastWith(MyObjectCaster::class)]
final class MyObject
{
    public function __construct(public string $name)
    {
    }
}
