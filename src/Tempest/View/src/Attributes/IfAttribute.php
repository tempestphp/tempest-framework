<?php

declare(strict_types=1);

namespace Tempest\View\Attributes;

use Tempest\View\Attribute;
use Tempest\View\Element;
use Tempest\View\Elements\EmptyElement;

final readonly class IfAttribute implements Attribute
{
    public function apply(Element $element): Element
    {
        $condition = $element->getAttribute('if');

        if ($condition) {
            return $element;
        }

        return new EmptyElement();
    }
}
