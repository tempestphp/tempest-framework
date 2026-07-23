<?php

declare(strict_types=1);

namespace Tempest\Mcp\Tests;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Mcp\ArgumentBinder;
use Tempest\Mcp\Exceptions\ParametersWereInvalid;
use Tempest\Mcp\SchemaGenerator;
use Tempest\Mcp\Tests\Fixtures\SchemaFixture;
use Tempest\Mcp\Tests\Fixtures\Suit;
use Tempest\Reflection\MethodReflector;
use Tempest\Validation\Validator;

/**
 * @internal
 */
final class ArgumentBinderTest extends TestCase
{
    #[Test]
    public function binds_scalar_arguments_without_altering_them(): void
    {
        $bound = $this->binder()->bind($this->method('scalars'), [
            'a' => 'text',
            'b' => 42,
            'c' => 1.5,
            'd' => true,
            'e' => ['x'],
        ]);

        $this->assertSame(['a' => 'text', 'b' => 42, 'c' => 1.5, 'd' => true, 'e' => ['x']], $bound);
    }

    #[Test]
    public function casts_enum_arguments_to_enum_instances(): void
    {
        $bound = $this->binder()->bind($this->method('enums'), ['suit' => 'hearts']);

        $this->assertSame(['suit' => Suit::HEARTS], $bound);
    }

    #[Test]
    public function omits_absent_arguments_with_defaults(): void
    {
        $bound = $this->binder()->bind($this->method('optionals'), []);

        $this->assertSame(['filter' => null], $bound);
        $this->assertArrayNotHasKey('limit', $bound);
        $this->assertArrayNotHasKey('sort', $bound);
    }

    #[Test]
    public function preserves_explicitly_provided_null_values(): void
    {
        $bound = $this->binder()->bind($this->method('optionals'), ['limit' => 5, 'sort' => null]);

        $this->assertSame(['filter' => null, 'limit' => 5, 'sort' => null], $bound);
    }

    #[Test]
    public function injected_parameters_are_not_part_of_the_binding(): void
    {
        $bound = $this->binder()->bind($this->method('injected'), ['name' => 'a']);

        $this->assertSame(['name' => 'a'], $bound);
    }

    #[Test]
    public function injected_parameters_cannot_be_provided_by_the_client(): void
    {
        $this->expectException(ParametersWereInvalid::class);
        $this->expectExceptionMessage('The argument `service` is unknown');

        $this->binder()->bind($this->method('injected'), ['name' => 'a', 'service' => 'spoofed']);
    }

    #[Test]
    public function rejects_unknown_arguments(): void
    {
        $this->expectException(ParametersWereInvalid::class);
        $this->expectExceptionMessage('The argument `extra` is unknown');

        $this->binder()->bind($this->method('enums'), ['suit' => 'hearts', 'extra' => 1]);
    }

    #[Test]
    public function rejects_missing_required_arguments(): void
    {
        $this->expectException(ParametersWereInvalid::class);
        $this->expectExceptionMessage('The required argument `suit` is missing');

        $this->binder()->bind($this->method('enums'), []);
    }

    private function binder(): ArgumentBinder
    {
        return new ArgumentBinder(new SchemaGenerator(), new Validator());
    }

    private function method(string $name): MethodReflector
    {
        return MethodReflector::fromParts(SchemaFixture::class, $name);
    }
}
