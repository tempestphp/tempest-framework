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
class JsonToArrayMapperTest extends IntegrationTest
{
    public function test_mapper(): void
    {
        $array = map('["a"]')->to(MapTo::ARRAY);

        $this->assertSame(['a'], $array);
    }
}
