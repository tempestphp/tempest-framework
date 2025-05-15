<?php

namespace Tempest\Reflection\Tests\Fixtures;

use PHPUnit\Framework\TestCase;
use Tempest\Reflection\ClassReflector;

final class HasAttributeTest extends TestCase
{
    public function test_property_attribute(): void
    {
        $class = new ClassReflector(ClassWithPropertyWithAttributeImplementingPropertyAttribute::class);
        $property = $class->getProperty('prop');
        $attribute = $property->getAttribute(AttributeImplementingPropertyAttribute::class);

        $this->assertSame('prop', $attribute->property->getName());
    }
}
