<?php

declare(strict_types=1);

namespace Tempest\View\Attributes;

use Tempest\View\Attribute;
use Tempest\View\Element;

final readonly class DefaultAttribute implements Attribute
{
    public function apply(Element $element): Element
    {
        return $element;
    }
}
