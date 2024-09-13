<?php

declare(strict_types=1);

<<<<<<<< HEAD:tests/Integration/Mapper/Mappers/JsonToArrayMapperTestCase.php
namespace Tests\Tempest\Integration\Mapper\Mappers;
========
namespace Tempest\Mapper\Tests\Mappers;
>>>>>>>> main:src/Tempest/Mapper/tests/Mappers/JsonToArrayMapperTestCase.php

use function Tempest\map;
use Tempest\Mapper\MapTo;
use Tests\Tempest\Integration\IntegrationTestCase;

/**
 * @internal
 * @small
 */
class JsonToArrayMapperTestCase extends IntegrationTestCase
{
    public function test_mapper(): void
    {
        $array = map('["a"]')->to(MapTo::ARRAY);

        $this->assertSame(['a'], $array);
    }
}
