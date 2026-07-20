<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Mapper\Mappers;

use PHPUnit\Framework\Attributes\Test;
use Tempest\Mapper\Mappers\JsonToObjectMapper;
use Tempest\Mapper\MappingContext;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;
use Tests\Tempest\Integration\Mapper\Fixtures\ObjectA;

use function Tempest\Mapper\map;

/**
 * @internal
 */
final class JsonToObjectMapperTest extends FrameworkIntegrationTestCase
{
    #[Test]
    public function json_to_object(): void
    {
        $object = map('{"a":"a","b":"b"}')->to(ObjectA::class);

        $this->assertSame('a', $object->a);
        $this->assertSame('b', $object->b);
    }

    #[Test]
    public function invalid_json(): void
    {
        $mapper = new JsonToObjectMapper(MappingContext::default());

        $this->assertFalse($mapper->canMap('invalid', ObjectA::class));
    }

    #[Test]
    public function invalid_object(): void
    {
        $mapper = new JsonToObjectMapper(MappingContext::default());

        $this->assertFalse($mapper->canMap('{}', 'unknown'));
    }
}
