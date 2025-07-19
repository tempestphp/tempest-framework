<?php

namespace Tests\Tempest\Integration\Mapper\Casters;

use PHPUnit\Framework\Attributes\TestWith;
use Tempest\Mapper\Casters\BooleanCaster;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

final class BooleanCasterTest extends FrameworkIntegrationTestCase
{
    #[TestWith(['true', true])]
    #[TestWith(['false', false])]
    #[TestWith([true, true])]
    #[TestWith([false, false])]
    #[TestWith(['on', true])]
    #[TestWith(['enabled', true])]
    #[TestWith(['yes', true])]
    #[TestWith(['ON', true])]
    #[TestWith(['ENABLED', true])]
    #[TestWith(['YES', true])]
    #[TestWith(['off', false])]
    #[TestWith(['disabled', false])]
    #[TestWith(['no', false])]
    public function test_cast(mixed $input, bool $expected): void
    {
        $this->assertSame($expected, new BooleanCaster()->cast($input));
    }
}
