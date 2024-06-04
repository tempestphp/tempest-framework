<?php

declare(strict_types=1);

namespace Tests\Tempest\Unit\Mapper\Mappers;

use Tempest\Mapper\MapTo;
use Tests\Tempest\IntegrationTest;
use Tests\Tempest\Unit\Mapper\Fixtures\ObjectA;
use function Tempest\map;

/**
 * @internal
 * @small
 */
class ObjectToArrayMapperTest extends IntegrationTest
{
    public function test_object_to_array(): void
    {
        $array = map(new ObjectA('a', 'b'))->to(MapTo::ARRAY);

        $this->assertSame(['a' => 'a', 'b' => 'b'], $array);
    }
}
