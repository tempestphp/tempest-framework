<?php

declare(strict_types=1);

namespace Tempest\Support\Tests\Reflection;

use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Tempest\Database\DatabaseModel;
use Tempest\Support\Reflection\TypeReflector;
use Tests\Tempest\Fixtures\Models\A;
use Tests\Tempest\Fixtures\Models\B;
use Tests\Tempest\Fixtures\Models\C;

/**
 * @internal
 * @small
 */
class ReflectionTypeTest extends TestCase
{
    #[DataProvider('data')]
    public function test_accepts(string $type, mixed $input, bool $expected): void
    {
        $this->assertSame(
            expected: $expected,
            actual: (new TypeReflector($type))->accepts($input),
        );
    }

    public static function data(): Generator
    {
        yield ['string', 'test', true];
        yield ['string', [], false];
        yield ['string', 1, false];
        yield ['string|int', 1, true];
        yield [DatabaseModel::class, new A(new B(new C('test'))), true];
        yield [self::class, new A(new B(new C('test'))), false];
        yield ['string', null, false];
        yield ['?string', null, true];
    }
}
