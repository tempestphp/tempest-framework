<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Mapper\Mappers;

use function Tempest\map;
use Tempest\Mapper\MapTo;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
final class ArrayToJsonMapperTestCase extends FrameworkIntegrationTestCase
{
    public function test_mapper(): void
    {
        $json = map(['a'])->to(MapTo::JSON);

        $this->assertSame('["a"]', $json);
    }
}
