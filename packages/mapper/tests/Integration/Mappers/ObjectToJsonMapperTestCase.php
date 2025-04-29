<?php

declare(strict_types=1);

namespace Tempest\Mapper\Tests\Integration\Mappers;

use Tempest\Mapper\Tests\Integration\Fixtures\ObjectA;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

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
