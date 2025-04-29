<?php

declare(strict_types=1);

namespace Tempest\Mapper\Tests\Integration\Mappers;

use Tempest\Mapper\Mappers\JsonToObjectMapper;
use Tempest\Mapper\Tests\Integration\Fixtures\ObjectA;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

use function Tempest\map;

/**
 * @internal
 */
final class JsonToObjectMapperTestCase extends FrameworkIntegrationTestCase
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
