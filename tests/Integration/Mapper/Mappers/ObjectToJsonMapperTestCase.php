<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Mapper\Mappers;

use function Tempest\map;
use Tempest\Mapper\MapTo;
use Tests\Tempest\Integration\IntegrationTestCase;
use Tests\Tempest\Integration\Mapper\Fixtures\ObjectA;

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
