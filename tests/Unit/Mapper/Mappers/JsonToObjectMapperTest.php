<?php

declare(strict_types=1);

namespace Tests\Tempest\Unit\Mapper\Mappers;

use function Tempest\map;
use Tests\Tempest\IntegrationTest;
use Tests\Tempest\Unit\Mapper\Fixtures\ObjectA;

/**
 * @internal
 * @small
 */
class JsonToObjectMapperTest extends IntegrationTest
{
    public function test_json_to_object(): void
    {
        $object = map('{"a":"a","b":"b"}')->to(ObjectA::class);

        $this->assertSame('a', $object->a);
        $this->assertSame('b', $object->b);
    }
}
