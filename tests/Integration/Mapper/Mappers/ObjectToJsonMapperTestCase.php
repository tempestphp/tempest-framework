<?php

declare(strict_types=1);

<<<<<<<< HEAD:tests/Integration/Mapper/Mappers/ObjectToJsonMapperTestCase.php
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
>>>>>>>> main:src/Tempest/Mapper/tests/Mappers/ObjectToJsonMapperTestCase.php

/**
 * @internal
 * @small
 */
class ObjectToJsonMapperTestCase extends IntegrationTestCase
{
    public function test_object_to_json(): void
    {
        $json = map(new ObjectA('a', 'b'))->to(MapTo::JSON);

        $this->assertSame('{"a":"a","b":"b"}', $json);
    }
}
