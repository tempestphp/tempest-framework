<?php

declare(strict_types=1);

namespace Tempest {

    use ReflectionClass as PHPReflectionClass;
    use ReflectionProperty as PHPReflectionProperty;
    use Tempest\Support\Reflection\ClassReflector;
    use Tempest\Support\Reflection\PropertyReflector;

    function reflect(mixed $classOrProperty, ?string $propertyName = null): ClassReflector|PropertyReflector
    {
        if ($classOrProperty instanceof PHPReflectionClass) {
            return new ClassReflector($classOrProperty);
        }

        if ($classOrProperty instanceof PHPReflectionProperty) {
            return new PropertyReflector($classOrProperty);
        }

        if ($propertyName !== null) {
            return new PropertyReflector(new PHPReflectionProperty($classOrProperty, $propertyName));
        }

        return new ClassReflector($classOrProperty);
    }
}
