<?php

declare(strict_types=1);

<<<<<<<< HEAD:tests/Integration/Mapper/Fixtures/ObjectA.php
namespace Tests\Tempest\Integration\Mapper\Fixtures;
========
namespace Tempest\Mapper\Tests\Fixtures;
>>>>>>>> main:src/Tempest/Mapper/tests/Fixtures/ObjectA.php

use Tempest\Mapper\Strict;

#[Strict]
final class ObjectA
{
    public function __construct(
        public string $a,
        public string $b,
    ) {
    }
}
