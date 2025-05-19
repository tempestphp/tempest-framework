<?php

namespace Tempest\View\Attributes;

use Tempest\View\Attribute;
use Tempest\View\Element;

final class EscapedExpressionAttribute implements Attribute
{
    public function __construct(
        private string $name,
    ) {}

    public function apply(Element $element): ?Element
    {
        $attributeValue = $element->getAttribute($this->name);

        $element
            ->addRawAttribute(sprintf(':%s="%s"', ltrim($this->name, ':'), $attributeValue))
            ->unsetAttribute($this->name);

        return $element;
    }
}
