<?php

declare(strict_types=1);

namespace Tempest\Support\Tests\Conditions;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Support\Conditions\HasConditions;

/**
 * @internal
 */
final class HasConditionsTest extends TestCase
{
    #[Test]
    public function when(): void
    {
        $class = new class() {
            use HasConditions;

            public bool $value = false;
        };

        $class->when(true, fn ($c) => $c->value = true);

        $this->assertTrue($class->value);
    }

    #[Test]
    public function when_with_callback(): void
    {
        $class = new class() {
            use HasConditions;

            public bool $value = false;
        };

        $class->when(fn () => true, fn ($c) => $c->value = true);

        $this->assertTrue($class->value);
    }

    #[Test]
    public function unless(): void
    {
        $class = new class() {
            use HasConditions;

            public bool $value = false;
        };

        $class->unless(true, fn ($c) => $c->value = true);

        $this->assertFalse($class->value);
    }

    #[Test]
    public function unless_with_callback(): void
    {
        $class = new class() {
            use HasConditions;

            public bool $value = false;
        };

        $class->unless(fn () => true, fn ($c) => $c->value = true);

        $this->assertFalse($class->value);
    }

    #[Test]
    public function returns_same_instance(): void
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

        $class->when(true, function ($c): void {
            $c->append('bar');
        });

        $this->assertSame('foo', $class->string);
    }
}
