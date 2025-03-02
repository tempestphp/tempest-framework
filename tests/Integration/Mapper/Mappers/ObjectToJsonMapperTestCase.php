<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Mapper\Mappers;

use Tests\Tempest\Integration\FrameworkIntegrationTestCase;
use Tests\Tempest\Integration\Mapper\Fixtures\ObjectA;
use function Tempest\map;

/**
 * @internal
 */
final class ObjectToJsonMapperTestCase extends FrameworkIntegrationTestCase
{
    public function test_object_to_json(): void
    {
        $json = map(new ObjectA('a', 'b'))->toJson();

        $this->assertSame('{"a":"a","b":"b"}', $json);
    }
}
