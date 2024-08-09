<?php

declare(strict_types=1);

namespace Tempest\View\Attributes;

use Tempest\View\Attribute;
use Tempest\View\Element;
use Tempest\View\Elements\EmptyElement;
use Tempest\View\Elements\GenericElement;

final readonly class IfAttribute implements Attribute
{
    public function apply(Element $element): Element
    {
        if (! $element instanceof GenericElement) {
            return $element;
        }

        $condition = $element->getAttribute('if');

        if ($condition) {
            return $element;
        }

        return new EmptyElement();
    }
}
