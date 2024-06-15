<?php

namespace Tempest\View\Attributes;

use Tempest\View\Attribute;
use Tempest\View\Element;

final readonly class ForeachAttribute implements Attribute
{
    public function __construct(
        private string $eval,
    ) {}

    public function apply(Element $element): Element
    {
        return $element;
    }
}