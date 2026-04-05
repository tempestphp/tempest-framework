<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Mapper\Casters;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use Tempest\Mapper\Casters\FloatCaster;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

final class FloatCasterTest extends FrameworkIntegrationTestCase
{
    #[Test]
    #[TestWith(['3.14', 3.14])]
    #[TestWith([3.14, 3.14])]
    #[TestWith(['0', 0.0])]
    #[TestWith([0, 0.0])]
    #[TestWith(['', 0.0])]
    #[TestWith([null, 0.0])]
    public function cast(mixed $input, float $expected): void
    {
        $caster = new FloatCaster();

        $this->assertSame($expected, $caster->cast($input));
    }

    #[Test]
    #[TestWith(['3.14', 3.14])]
    #[TestWith([3.14, 3.14])]
    #[TestWith(['0', 0.0])]
    #[TestWith([0, 0.0])]
    #[TestWith(['  3.14  ', 3.14])]
    public function nullable_cast_with_values(mixed $input, float $expected): void
    {
        $caster = new FloatCaster(nullable: true);

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
        $caster = new FloatCaster(nullable: true);

        $this->assertNull($caster->cast($input));
    }
}
