<?php

declare(strict_types=1);

<<<<<<<< HEAD:tests/Integration/Mapper/Mappers/ArrayToJsonMapperTestCase.php
namespace Tests\Tempest\Integration\Mapper\Mappers;
========
namespace Tempest\Mapper\Tests\Mappers;
>>>>>>>> main:src/Tempest/Mapper/tests/Mappers/ArrayToJsonMapperTestCase.php

use function Tempest\map;
use Tempest\Mapper\MapTo;
use Tests\Tempest\Integration\IntegrationTestCase;

/**
 * @internal
 * @small
 */
class ArrayToJsonMapperTestCase extends IntegrationTestCase
{
    public function test_mapper(): void
    {
        $json = map(['a'])->to(MapTo::JSON);

        $this->assertSame('["a"]', $json);
    }
}
