<?php

declare(strict_types=1);

namespace Tempest\Reflection\Tests\Unit;

use ArrayIterator;
use DateTimeImmutable;
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Stringable;
use Tempest\Reflection\Tests\Unit\Fixtures\A;
use Tempest\Reflection\Tests\Unit\Fixtures\B;
use Tempest\Reflection\Tests\Unit\Fixtures\C;
use Tempest\Reflection\Tests\Unit\Fixtures\TestEnum;
use Tempest\Reflection\TypeReflector;
use Tempest\Support\Arr\ImmutableArray;
use Tempest\Support\Str\ImmutableString;

/**
 * @internal
 */
final class ReflectionTypeTest extends TestCase
{
    #[DataProvider('data')]
    public function test_accepts(string $type, mixed $input, bool $expected): void
    {
        $this->assertSame(
            expected: $expected,
            actual: new TypeReflector($type)->accepts($input),
        );
    }

    public static function data(): Generator
    {
        yield ['string', 'test', true];
        yield ['string', [], false];
        yield ['string', 1, false];
        yield ['string|int', 1, true];
        // TODO: We will have to add a comparable test to this.
        //        yield [DatabaseModel::class, new A(new B(new C('test'))), true];
        yield [self::class, new A(new B(new C('test'))), false];
        yield ['string', null, false];
        yield ['?string', null, true];
    }

    public function test_as_class(): void
    {
        $this->assertSame(
            expected: A::class,
            actual: new TypeReflector(A::class)->asClass()->getName(),
        );

        $this->assertSame(
            expected: A::class,
            actual: new TypeReflector('?Tempest\Reflection\Tests\Unit\Fixtures\A')->asClass()->getName(),
        );
    }

    public function test_is_scalar(): void
    {
        $this->assertTrue(new TypeReflector('bool')->isScalar());
        $this->assertTrue(new TypeReflector('string')->isScalar());
        $this->assertTrue(new TypeReflector('int')->isScalar());
        $this->assertTrue(new TypeReflector('float')->isScalar());
        $this->assertFalse(new TypeReflector('array')->isScalar());
        $this->assertFalse(new TypeReflector('object')->isScalar());
        $this->assertFalse(new TypeReflector(A::class)->isScalar());
    }

    public function test_is_enum(): void
    {
        $this->assertTrue(new TypeReflector(TestEnum::class)->isEnum());
        $this->assertFalse(new TypeReflector(A::class)->isEnum());
    }

    public function test_is_iterable(): void
    {
        $this->assertTrue(new TypeReflector(ArrayIterator::class)->isIterable());
        $this->assertTrue(new TypeReflector('array')->isIterable());
        $this->assertFalse(new TypeReflector('string')->isIterable());
    }

    public function test_is_stringable(): void
    {
        $this->assertTrue(new TypeReflector('string')->isStringable());
        $this->assertTrue(new TypeReflector(Stringable::class)->isStringable());
        $this->assertFalse(new TypeReflector(ArrayIterator::class)->isStringable());
        $this->assertFalse(new TypeReflector('array')->isStringable());
    }

    public function test_is_relation(): void
    {
        $this->assertTrue(new TypeReflector(A::class)->isRelation());
        $this->assertFalse(new TypeReflector(TestEnum::class)->isRelation());
        $this->assertFalse(new TypeReflector('string')->isRelation());
        $this->assertFalse(new TypeReflector('array')->isRelation());
        $this->assertFalse(new TypeReflector(ImmutableArray::class)->isRelation());
        $this->assertFalse(new TypeReflector(ImmutableString::class)->isRelation());
        $this->assertFalse(new TypeReflector(DateTimeImmutable::class)->isRelation());
    }
}
