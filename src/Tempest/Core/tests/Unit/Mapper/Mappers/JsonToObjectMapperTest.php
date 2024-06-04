<?php

declare(strict_types=1);

namespace Tests\Tempest\Unit\Mapper\Mappers;

use Tempest\Mapper\Mappers\JsonToObjectMapper;
use Tests\Tempest\IntegrationTest;
use Tests\Tempest\Unit\Mapper\Fixtures\ObjectA;
use function Tempest\map;

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

    public function test_invalid_json(): void
    {
        $mapper = new JsonToObjectMapper();

        $this->assertFalse($mapper->canMap('invalid', ObjectA::class));
    }

    public function test_invalid_object(): void
    {
        $mapper = new JsonToObjectMapper();

        $this->assertFalse($mapper->canMap('{}', 'unknown'));
    }
}
