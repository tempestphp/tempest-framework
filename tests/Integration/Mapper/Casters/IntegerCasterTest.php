<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Mapper\Casters;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use Tempest\Mapper\Casters\IntegerCaster;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

final class IntegerCasterTest extends FrameworkIntegrationTestCase
{
    #[Test]
    #[TestWith(['42', 42])]
    #[TestWith([42, 42])]
    #[TestWith(['0', 0])]
    #[TestWith([0, 0])]
    #[TestWith(['', 0])]
    #[TestWith([null, 0])]
    public function cast(mixed $input, int $expected): void
    {
        $caster = new IntegerCaster();

        $this->assertSame($expected, $caster->cast($input));
    }

    #[Test]
    #[TestWith(['42', 42])]
    #[TestWith([42, 42])]
    #[TestWith(['0', 0])]
    #[TestWith([0, 0])]
    #[TestWith(['  42  ', 42])]
    public function nullable_cast_with_values(mixed $input, int $expected): void
    {
        $caster = new IntegerCaster(nullable: true);

        $this->assertSame($expected, $caster->cast($input));
    }

    #[Test]
    #[TestWith([null])]
    #[TestWith([''])]
    #[TestWith(['null'])]
    #[TestWith(['NULL'])]
    #[TestWith(['Null'])]
    #[TestWith(['   '])]
    public function nullable_cast_returns_null_for_empty_input(mixed $input): void
    {
        $caster = new IntegerCaster(nullable: true);

        $this->assertNull($caster->cast($input));
    }
}
