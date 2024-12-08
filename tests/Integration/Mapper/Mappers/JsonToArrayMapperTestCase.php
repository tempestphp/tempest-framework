<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Mapper\Mappers;

use Tempest\Mapper\MapTo;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;
use function Tempest\map;

/**
 * @internal
 */
final class JsonToArrayMapperTestCase extends FrameworkIntegrationTestCase
{
    public function test_mapper(): void
    {
        $array = map('["a"]')->to(MapTo::ARRAY);

        $this->assertSame(['a'], $array);
    }
}
