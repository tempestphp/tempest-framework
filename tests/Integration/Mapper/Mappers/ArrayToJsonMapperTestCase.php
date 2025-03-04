<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Mapper\Mappers;

use Tests\Tempest\Integration\FrameworkIntegrationTestCase;
use function Tempest\map;

/**
 * @internal
 */
final class ArrayToJsonMapperTestCase extends FrameworkIntegrationTestCase
{
    public function test_mapper(): void
    {
        $json = map(['a'])->toJson();

        $this->assertSame('["a"]', $json);
    }
}
