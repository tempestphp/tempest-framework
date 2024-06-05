<?php

declare(strict_types=1);

namespace Tests\Tempest\Unit\Mapper\Mappers;

use function Tempest\map;
use Tempest\Mapper\MapTo;
use Tests\Tempest\Unit\IntegrationTestCase;
use Tests\Tempest\Unit\Mapper\Fixtures\ObjectA;

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
