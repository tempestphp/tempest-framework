<?php

declare(strict_types=1);

namespace Tempest\Mapper\Tests\Mappers;

use function Tempest\map;
use Tempest\Mapper\MapTo;
use Tempest\Mapper\Tests\Fixtures\ObjectA;
use Tests\Tempest\Integration\IntegrationTestCase;

/**
 * @internal
 * @small
 */
final class ObjectToJsonMapperTestCase extends IntegrationTestCase
{
    public function test_object_to_json(): void
    {
        $json = map(new ObjectA('a', 'b'))->to(MapTo::JSON);

        $this->assertSame('{"a":"a","b":"b"}', $json);
    }
}
