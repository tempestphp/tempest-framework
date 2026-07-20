<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Mapper\Mappers;

use PHPUnit\Framework\Attributes\Test;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;
use Tests\Tempest\Integration\Mapper\Fixtures\ObjectA;

use function Tempest\Mapper\map;

/**
 * @internal
 */
final class ObjectToJsonMapperTest extends FrameworkIntegrationTestCase
{
    #[Test]
    public function object_to_json(): void
    {
        $json = map(new ObjectA('a', 'b'))->toJson();

        $this->assertSame('{"a":"a","b":"b"}', $json);
    }
}
