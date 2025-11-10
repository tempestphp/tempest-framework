<?php

namespace Tests\Tempest\Integration\Mapper\Casters;

use PHPUnit\Framework\Attributes\TestWith;
use Tempest\Mapper\Casters\EnumCaster;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;
use Tests\Tempest\Integration\Mapper\Fixtures\BackedEnumToSerialize;
use Tests\Tempest\Integration\Mapper\Fixtures\UnitEnumToSerialize;
use UnitEnum;

final class EnumCasterTest extends FrameworkIntegrationTestCase
{
    #[TestWith(['FOO', UnitEnumToSerialize::FOO, UnitEnumToSerialize::class])]
    #[TestWith(['BAR', UnitEnumToSerialize::BAR, UnitEnumToSerialize::class])]
    #[TestWith(['foo', BackedEnumToSerialize::FOO, BackedEnumToSerialize::class])]
    #[TestWith(['bar', BackedEnumToSerialize::BAR, BackedEnumToSerialize::class])]
    public function test_cast(mixed $input, UnitEnum $expected, string $class): void
    {
        $this->assertSame($expected, new EnumCaster($class)->cast($input));
    }
}
