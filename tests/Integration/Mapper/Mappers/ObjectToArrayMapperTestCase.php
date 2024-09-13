<?php

declare(strict_types=1);

<<<<<<<< HEAD:tests/Integration/Mapper/Mappers/ObjectToArrayMapperTestCase.php
namespace Tests\Tempest\Integration\Mapper\Mappers;

use function Tempest\map;
use Tempest\Mapper\MapTo;
use Tests\Tempest\Integration\IntegrationTestCase;
use Tests\Tempest\Integration\Mapper\Fixtures\ObjectA;
========
namespace Tempest\Mapper\Tests\Mappers;

use function Tempest\map;
use Tempest\Mapper\MapTo;
use Tempest\Mapper\Tests\Fixtures\ObjectA;
use Tests\Tempest\Unit\IntegrationTestCase;
>>>>>>>> main:src/Tempest/Mapper/tests/Mappers/ObjectToArrayMapperTestCase.php

/**
 * @internal
 * @small
 */
class ObjectToArrayMapperTestCase extends IntegrationTestCase
{
    public function test_object_to_array(): void
    {
        $array = map(new ObjectA('a', 'b'))->to(MapTo::ARRAY);

        $this->assertSame(['a' => 'a', 'b' => 'b'], $array);
    }
}
