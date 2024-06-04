<?php

declare(strict_types=1);

namespace Tests\Tempest\Unit\Mapper\Mappers;

use Tempest\Mapper\MapTo;
use Tests\Tempest\IntegrationTest;
use function Tempest\map;

/**
 * @internal
 * @small
 */
class ArrayToJsonMapperTest extends IntegrationTest
{
    public function test_mapper(): void
    {
        $json = map(['a'])->to(MapTo::JSON);

        $this->assertSame('["a"]', $json);
    }
}
