<?php

declare(strict_types=1);

namespace Tempest\Support\Tests\Conditions;

use PHPUnit\Framework\TestCase;
use Tempest\Support\Conditions\HasConditions;

/**
 * @internal
 */
final class HasConditionsTest extends TestCase
{
    public function test_when(): void
    {
        $class = new class() {
            use HasConditions;

            public bool $value = false;
        };

        $class->when(true, static fn ($c) => $c->value = true);

        $this->assertTrue($class->value);
    }

    public function test_when_with_callback(): void
    {
        $class = new class() {
            use HasConditions;

            public bool $value = false;
        };

        $class->when(static fn () => true, static fn ($c) => $c->value = true);

        $this->assertTrue($class->value);
    }

    public function test_unless(): void
    {
        $class = new class() {
            use HasConditions;

            public bool $value = false;
        };

        $class->unless(true, static fn ($c) => $c->value = true);

        $this->assertFalse($class->value);
    }

    public function test_unless_with_callback(): void
    {
        $class = new class() {
            use HasConditions;

            public bool $value = false;
        };

        $class->unless(static fn () => true, static fn ($c) => $c->value = true);

        $this->assertFalse($class->value);
    }

    public function test_returns_same_instance(): void
    {
        $class = new class() {
            use HasConditions;

            public string $string = 'foo';

            public function append(string $string): self
            {
                $self = new self();
                $self->string = $this->string . $string;

                return $self;
            }
        };

        $class->when(true, static function ($c): void {
            $c->append('bar');
        });

        $this->assertSame('foo', $class->string);
    }
}
