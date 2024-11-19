<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Mapper\Mappers;

use Tests\Tempest\Integration\Mapper\Fixtures\ObjectA;
use function Tempest\map;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
final class JsonFileToObjectMapperTest extends FrameworkIntegrationTestCase
{
    public function test_mapper(): void
    {
        $objects = map(__DIR__ . '/../Fixtures/objects.json')->collection()->to(ObjectA::class);

        $this->assertCount(2, $objects);
        $this->assertSame('a', $objects[0]->a);
        $this->assertSame('b', $objects[0]->b);
        $this->assertSame('c', $objects[1]->a);
        $this->assertSame('d', $objects[1]->b);
    }
}
