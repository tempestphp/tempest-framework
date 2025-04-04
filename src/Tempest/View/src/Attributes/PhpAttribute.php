<?php

namespace Tempest\View\Attributes;

use Tempest\View\Attribute;
use Tempest\View\Element;

final readonly class PhpAttribute implements Attribute
{
    public function __construct(
        private string $index,
        private string $content,
    ) {}

    public function apply(Element $element): Element
    {
        $element
            ->addRawAttribute($this->content)
            ->unsetAttribute($this->index);

        return $element;
    }
}
