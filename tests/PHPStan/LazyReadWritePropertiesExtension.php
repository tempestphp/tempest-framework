<?php

declare(strict_types=1);

namespace Tests\Tempest\PHPStan;

use PHPStan\Reflection\ExtendedPropertyReflection;
use PHPStan\Rules\Properties\ReadWritePropertiesExtension;
use Tempest\Container\Inject;
use Tempest\Database\Lazy;

final readonly class LazyReadWritePropertiesExtension implements ReadWritePropertiesExtension
{
    public function isAlwaysRead(ExtendedPropertyReflection $property, string $propertyName): bool
    {
        return false;
    }

    public function isAlwaysWritten(ExtendedPropertyReflection $property, string $propertyName): bool
    {
        return false;
    }

    public function isInitialized(ExtendedPropertyReflection $property, string $propertyName): bool
    {
        foreach ($property->getAttributes() as $attribute) {
            $attributeName = ltrim($attribute->getName(), '\\');

            if ($attributeName === Lazy::class || $attributeName === Inject::class) {
                return true;
            }
        }

        return false;
    }
}
