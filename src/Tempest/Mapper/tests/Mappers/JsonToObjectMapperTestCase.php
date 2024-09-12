<?php

declare(strict_types=1);

namespace Tempest\Mapper\Tests\Mappers;

use function Tempest\map;
use Tempest\Mapper\Mappers\JsonToObjectMapper;
use Tempest\Mapper\Tests\Fixtures\ObjectA;
use Tests\Tempest\Unit\IntegrationTestCase;

/**
 * @internal
 * @small
 */
class JsonToObjectMapperTestCase extends IntegrationTestCase
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
