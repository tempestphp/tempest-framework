<?php

declare(strict_types=1);

namespace Tempest\View\Attributes;

use Tempest\View\Attribute;
use Tempest\View\Element;
use Tempest\View\Elements\PhpIfElement;

final readonly class IfAttribute implements Attribute
{
    public function apply(Element $element): Element
    {
        return new PhpIfElement($element);
    }
}
