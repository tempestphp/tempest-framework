<?php

namespace Tests\Tempest\Integration\Mapper\Casters;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use Tempest\Mapper\Casters\EnumCaster;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;
use Tests\Tempest\Integration\Mapper\Fixtures\BackedEnumToSerialize;
use Tests\Tempest\Integration\Mapper\Fixtures\UnitEnumToSerialize;
use UnitEnum;

final class EnumCasterTest extends FrameworkIntegrationTestCase
{
    #[Test]
    #[TestWith(['FOO', UnitEnumToSerialize::FOO, UnitEnumToSerialize::class])]
    #[TestWith(['BAR', UnitEnumToSerialize::BAR, UnitEnumToSerialize::class])]
    #[TestWith(['foo', BackedEnumToSerialize::FOO, BackedEnumToSerialize::class])]
    #[TestWith(['bar', BackedEnumToSerialize::BAR, BackedEnumToSerialize::class])]
    public function cast(mixed $input, UnitEnum $expected, string $class): void
    {
        $this->assertSame($expected, new EnumCaster($class)->cast($input));
    }

    #[Test]
    #[TestWith([null])]
    #[TestWith([''])]
    #[TestWith(['null'])]
    #[TestWith(['NULL'])]
    #[TestWith(['   '])]
    public function nullable_cast_returns_null_for_empty_input(mixed $input): void
    {
        $caster = new EnumCaster(enum: BackedEnumToSerialize::class, nullable: true);

        $this->assertNull($caster->cast($input));
    }

    #[Test]
    #[TestWith(['foo', BackedEnumToSerialize::FOO])]
    #[TestWith(['bar', BackedEnumToSerialize::BAR])]
    public function nullable_cast_with_valid_values(mixed $input, UnitEnum $expected): void
    {
        $caster = new EnumCaster(enum: BackedEnumToSerialize::class, nullable: true);

        $this->assertSame($expected, $caster->cast($input));
    }
}
