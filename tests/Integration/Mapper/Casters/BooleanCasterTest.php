<?php

namespace Tests\Tempest\Integration\Mapper\Casters;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use Tempest\Mapper\Casters\BooleanCaster;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

final class BooleanCasterTest extends FrameworkIntegrationTestCase
{
    #[Test]
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
    public function cast(mixed $input, bool $expected): void
    {
        $this->assertSame($expected, new BooleanCaster()->cast($input));
    }

    #[Test]
    #[TestWith(['true', true])]
    #[TestWith([true, true])]
    #[TestWith(['false', false])]
    #[TestWith([false, false])]
    #[TestWith(['on', true])]
    #[TestWith(['off', false])]
    public function nullable_cast_with_values(mixed $input, bool $expected): void
    {
        $this->assertSame($expected, new BooleanCaster(nullable: true)->cast($input));
    }

    #[Test]
    #[TestWith([null])]
    #[TestWith([''])]
    #[TestWith(['null'])]
    #[TestWith(['   '])]
    public function nullable_cast_returns_null_for_empty_input(mixed $input): void
    {
        $this->assertNull(new BooleanCaster(nullable: true)->cast($input));
    }
}
