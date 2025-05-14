<?php

declare(strict_types=1);

namespace Tempest\Database;

use Attribute;
use Tempest\Reflection\PropertyAttribute;
use Tempest\Reflection\PropertyReflector;

#[Attribute(Attribute::TARGET_PROPERTY)]
final class HasMany implements PropertyAttribute
{
    public PropertyReflector $property;

    public string $fieldName {
        get => $this->property->getName() . '.' . $this->localPropertyName;
    }

    /** @param null|class-string $inverseClassName */
    public function __construct(
        public string $inversePropertyName,
        public ?string $inverseClassName = null,
        public string $localPropertyName = 'id',
    ) {}
}
