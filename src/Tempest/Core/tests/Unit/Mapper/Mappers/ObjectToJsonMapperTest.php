<?php

declare(strict_types=1);

namespace Tests\Tempest\Unit\Mapper\Mappers;

use Tempest\Mapper\MapTo;
use Tests\Tempest\IntegrationTest;
use Tests\Tempest\Unit\Mapper\Fixtures\ObjectA;
use function Tempest\map;

/**
 * @internal
 * @small
 */
class ObjectToJsonMapperTest extends IntegrationTest
{
    public function test_object_to_json(): void
    {
        $json = map(new ObjectA('a', 'b'))->to(MapTo::JSON);

        $this->assertSame('{"a":"a","b":"b"}', $json);
    }
}
