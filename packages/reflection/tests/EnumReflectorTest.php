<?php

declare(strict_types=1);

namespace Tempest\Reflection\Tests;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionEnum;
use Tempest\Reflection\EnumReflector;
use Tempest\Reflection\TypeReflector;

/**
 * @internal
 */
final class EnumReflectorTest extends TestCase
{
    #[Test]
    public function getting_underlying_reflection_enum(): void
    {
        $reflector = new EnumReflector(TestUnitEnum::class);

        $this->assertEquals(new ReflectionEnum(TestUnitEnum::class), $reflector->getReflection());
    }

    #[Test]
    public function getting_name(): void
    {
        $reflector = new EnumReflector(TestUnitEnum::class);
        $reflection = new ReflectionEnum(TestUnitEnum::class);

        $this->assertSame($reflector->getName(), $reflection->getName());
    }

    #[Test]
    public function getting_short_name(): void
    {
        $reflector = new EnumReflector(TestUnitEnum::class);
        $reflection = new ReflectionEnum(TestUnitEnum::class);

        $this->assertSame($reflector->getShortName(), $reflection->getShortName());
    }

    #[Test]
    public function getting_file_name(): void
    {
        $reflector = new EnumReflector(TestUnitEnum::class);

        $this->assertNotFalse($reflector->getFileName());
        $this->assertStringEndsWith('EnumReflectorTest.php', $reflector->getFileName());
    }

    #[Test]
    public function getting_type(): void
    {
        $reflector = new EnumReflector(TestBackedEnum::class);
        $type = $reflector->getType();

        $this->assertInstanceOf(TypeReflector::class, $type);
        $this->assertSame(TestBackedEnum::class, $type->getName());
    }

    #[Test]
    public function is_backed_for_unit_enum(): void
    {
        $reflector = new EnumReflector(TestUnitEnum::class);

        $this->assertFalse($reflector->isBacked());
    }

    #[Test]
    public function is_backed_for_backed_enum(): void
    {
        $reflector = new EnumReflector(TestBackedEnum::class);

        $this->assertTrue($reflector->isBacked());
    }

    #[Test]
    public function get_backing_type_for_unit_enum(): void
    {
        $reflector = new EnumReflector(TestUnitEnum::class);

        $this->assertNull($reflector->getBackingType());
    }

    #[Test]
    public function get_backing_type_for_backed_enum(): void
    {
        $reflector = new EnumReflector(TestBackedEnum::class);
        $backingType = $reflector->getBackingType();

        $this->assertInstanceOf(TypeReflector::class, $backingType);
        $this->assertSame('string', $backingType->getName());
    }

    #[Test]
    public function get_backing_type_for_int_backed_enum(): void
    {
        $reflector = new EnumReflector(TestIntBackedEnum::class);
        $backingType = $reflector->getBackingType();

        $this->assertInstanceOf(TypeReflector::class, $backingType);
        $this->assertSame('int', $backingType->getName());
    }

    #[Test]
    public function get_cases(): void
    {
        $reflector = new EnumReflector(TestUnitEnum::class);
        $cases = $reflector->getCases();

        $this->assertCount(3, $cases);
        $this->assertSame(TestUnitEnum::ONE, $cases[0]);
        $this->assertSame(TestUnitEnum::TWO, $cases[1]);
        $this->assertSame(TestUnitEnum::THREE, $cases[2]);
    }

    #[Test]
    public function get_cases_for_backed_enum(): void
    {
        $reflector = new EnumReflector(TestBackedEnum::class);
        $cases = $reflector->getCases();

        $this->assertCount(2, $cases);
        $this->assertSame(TestBackedEnum::ACTIVE, $cases[0]);
        $this->assertSame(TestBackedEnum::INACTIVE, $cases[1]);
    }

    #[Test]
    public function has_case(): void
    {
        $reflector = new EnumReflector(TestUnitEnum::class);

        $this->assertTrue($reflector->hasCase('ONE'));
        $this->assertTrue($reflector->hasCase('TWO'));
        $this->assertTrue($reflector->hasCase('THREE'));
        $this->assertFalse($reflector->hasCase('FOUR'));
    }

    #[Test]
    public function get_case(): void
    {
        $reflector = new EnumReflector(TestUnitEnum::class);

        $this->assertSame(TestUnitEnum::ONE, $reflector->getCase('ONE'));
        $this->assertSame(TestUnitEnum::TWO, $reflector->getCase('TWO'));
        $this->assertSame(TestUnitEnum::THREE, $reflector->getCase('THREE'));
    }

    #[Test]
    public function is_method(): void
    {
        $reflector = new EnumReflector(TestBackedEnum::class);

        $this->assertTrue($reflector->is(\BackedEnum::class));
        $this->assertTrue($reflector->is(\UnitEnum::class));
        $this->assertFalse($reflector->is(\stdClass::class));
    }

    #[Test]
    public function implements_method(): void
    {
        $reflector = new EnumReflector(TestEnumWithInterface::class);

        $this->assertTrue($reflector->implements(TestInterface::class));
        $this->assertFalse($reflector->implements(\JsonSerializable::class));
    }

    #[Test]
    public function serialize(): void
    {
        $reflector = new EnumReflector(TestUnitEnum::class);

        $serialized = serialize($reflector);
        $unserialized = unserialize($serialized);

        $this->assertEquals($reflector, $unserialized);
    }

    #[Test]
    public function from_type_reflector(): void
    {
        $typeReflector = new TypeReflector(TestBackedEnum::class);
        $enumReflector = $typeReflector->asEnum();

        $this->assertInstanceOf(EnumReflector::class, $enumReflector);
        $this->assertSame(TestBackedEnum::class, $enumReflector->getName());
        $this->assertTrue($enumReflector->isBacked());
    }

    #[Test]
    public function constructor_with_enum_instance(): void
    {
        $reflector = new EnumReflector(TestUnitEnum::ONE);

        $this->assertSame(TestUnitEnum::class, $reflector->getName());
    }

    #[Test]
    public function constructor_with_enum_reflector(): void
    {
        $reflector1 = new EnumReflector(TestUnitEnum::class);
        $reflector2 = new EnumReflector($reflector1);

        $this->assertEquals($reflector1, $reflector2);
    }

    #[Test]
    public function attribute_support(): void
    {
        $reflector = new EnumReflector(TestEnumWithAttribute::class);

        $this->assertTrue($reflector->hasAttribute(TestAttribute::class));
        $attribute = $reflector->getAttribute(TestAttribute::class);
        $this->assertInstanceOf(TestAttribute::class, $attribute);
        $this->assertSame('test-value', $attribute->value);
    }
}

enum TestUnitEnum
{
    case ONE;
    case TWO;
    case THREE;
}

enum TestBackedEnum: string
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
}

enum TestIntBackedEnum: int
{
    case FIRST = 1;
    case SECOND = 2;
}

interface TestInterface
{
}

enum TestEnumWithInterface: string implements TestInterface
{
    case VALUE = 'value';
}

#[\Attribute]
class TestAttribute
{
    public function __construct(
        public string $value,
    ) {}
}

#[TestAttribute('test-value')]
enum TestEnumWithAttribute
{
    case OPTION_A;
    case OPTION_B;
}
