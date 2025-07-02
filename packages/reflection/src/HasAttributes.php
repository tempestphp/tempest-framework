<?php

declare(strict_types=1);

namespace Tempest\Reflection;

use ReflectionAttribute;
use ReflectionAttribute as PHPReflectionAttribute;
use ReflectionClass as PHPReflectionClass;
use ReflectionMethod as PHPReflectionMethod;
use ReflectionParameter as PHPReflectionParameter;
use ReflectionProperty as PHPReflectionProperty;
use Tempest\Support\HasMemoization;

trait HasAttributes
{
    use HasMemoization;

    abstract public function getReflection(): PHPReflectionClass|PHPReflectionMethod|PHPReflectionProperty|PHPReflectionParameter;

    /**
     * @param class-string $name
     */
    public function hasAttribute(string $name): bool
    {
        return $this->getReflection()->getAttributes($name) !== [];
    }

    /**
     * @template TAttributeClass of object
     * @param class-string<TAttributeClass> $attributeClass
     * @return TAttributeClass|null
     */
    public function getAttribute(string $attributeClass, bool $recursive = false): ?object
    {
        $attribute = $this->getReflection()->getAttributes($attributeClass, PHPReflectionAttribute::IS_INSTANCEOF)[0] ?? null;

        $attributeInstance = $this->instantiate($attribute);

        if ($attributeInstance || ! $recursive) {
            return $attributeInstance;
        }

        if ($this instanceof ClassReflector) {
            foreach ($this->getInterfaces() as $interface) {
                $attributeInstance = $interface->asClass()->getAttribute($attributeClass);

                if ($attributeInstance !== null) {
                    break;
                }
            }

            if ($attributeInstance === null && ($parent = $this->getParent())) {
                $attributeInstance = $parent->getAttribute($attributeClass, true);
            }
        }

        return $attributeInstance;
    }

    /**
     * @template TAttributeClass of object
     * @param class-string<TAttributeClass> $attributeClass
     * @return TAttributeClass[]
     */
    public function getAttributes(string $attributeClass): array
    {
        return array_map(
            fn (PHPReflectionAttribute $attribute) => $this->instantiate($attribute),
            $this->getReflection()->getAttributes($attributeClass, PHPReflectionAttribute::IS_INSTANCEOF),
        );
    }

    private function instantiate(?ReflectionAttribute $attribute): ?object
    {
        $object = $attribute?->newInstance();

        if (! $object) {
            return null;
        }

        if ($object instanceof PropertyAttribute && $this instanceof PropertyReflector) {
            $object->property = $this;
        }

        return $object;
    }
}
