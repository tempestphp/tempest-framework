<?php

namespace Tempest\Reflection\Tests\Fixtures;

final class ClassWithPropertyWithAttributeImplementingPropertyAttribute
{
    #[AttributeImplementingPropertyAttribute]
    public string $prop;
}
