<?php

declare(strict_types=1);

namespace Tempest\Database;

use Attribute;
use Tempest\Reflection\PropertyAttribute;
use Tempest\Reflection\PropertyReflector;

#[Attribute(Attribute::TARGET_PROPERTY)]
final class BelongsTo implements PropertyAttribute
{
    public PropertyReflector $property;

    public function __construct(
        public string $localPropertyName,
        public string $inversePropertyName = 'id',
    ) {}
}
