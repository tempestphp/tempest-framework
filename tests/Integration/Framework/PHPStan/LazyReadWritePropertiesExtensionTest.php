<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Framework\PHPStan;

use PHPStan\Reflection\ExtendedPropertyReflection;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Container\Inject;
use Tempest\Database\Lazy;
use Tests\Tempest\PHPStan\LazyReadWritePropertiesExtension;

final class LazyReadWritePropertiesExtensionTest extends TestCase
{
    #[Test]
    public function marks_properties_with_lazy_attribute_as_initialized(): void
    {
        $extension = new LazyReadWritePropertiesExtension();
        $property = $this->createPropertyWithAttributes($this->createAttribute(Lazy::class));

        $this->assertTrue($extension->isInitialized($property, 'property'));
    }

    #[Test]
    public function marks_properties_with_inject_attribute_as_initialized(): void
    {
        $extension = new LazyReadWritePropertiesExtension();
        $property = $this->createPropertyWithAttributes($this->createAttribute(Inject::class));

        $this->assertTrue($extension->isInitialized($property, 'property'));
    }

    #[Test]
    public function does_not_mark_other_properties_as_initialized(): void
    {
        $extension = new LazyReadWritePropertiesExtension();
        $property = $this->createPropertyWithAttributes($this->createAttribute('Some\\Other\\Attribute'));

        $this->assertFalse($extension->isInitialized($property, 'property'));
    }

    private function createPropertyWithAttributes(object ...$attributes): object
    {
        $property = $this->createStub(ExtendedPropertyReflection::class);
        $property->method('getAttributes')->willReturn($attributes);

        return $property;
    }

    private function createAttribute(string $name): object
    {
        return new readonly class($name) {
            public function __construct(
                private string $name,
            ) {}

            public function getName(): string
            {
                return $this->name;
            }
        };
    }
}
