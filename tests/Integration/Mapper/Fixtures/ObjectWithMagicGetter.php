<?php

declare(strict_types=1);

<<<<<<<< HEAD:tests/Integration/Mapper/Fixtures/ObjectWithMagicGetter.php
namespace Tests\Tempest\Integration\Mapper\Fixtures;
========
namespace Tempest\Mapper\Tests\Fixtures;
>>>>>>>> main:src/Tempest/Mapper/tests/Fixtures/ObjectWithMagicGetter.php

final class ObjectWithMagicGetter
{
    public function __construct(
        public string $a,
    ) {
    }

    public function __get(string $name)
    {
        return 'magic';
    }
}
