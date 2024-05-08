<?php

namespace App\Mapper\Mappers;

use Tempest\Mapper\To;
use Tests\Tempest\IntegrationTest;
use function Tempest\map;

class ArrayToJsonMapperTest extends IntegrationTest
{
    public function test_mapper(): void
    {
        $json = map(['a'])->to(To::JSON);

        $this->assertSame('["a"]', $json);
    }
}
