<?php

declare(strict_types=1);

namespace Tests\Tempest\Unit\Mapper\Mappers;

use function Tempest\map;
use Tempest\Mapper\MapTo;
use Tests\Tempest\Unit\IntegrationTestCase;

/**
 * @internal
 * @small
 */
class JsonToArrayMapperTestCase extends IntegrationTestCase
{
    public function test_mapper(): void
    {
        $array = map('["a"]')->to(MapTo::ARRAY);

        $this->assertSame(['a'], $array);
    }
}
