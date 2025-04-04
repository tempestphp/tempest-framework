<?php

namespace Tempest\View\Attributes;

use Tempest\View\Attribute;
use Tempest\View\Element;

/*
 * This class is used whenever PHP code occurs within element tags, it will make sure this code is left untouched
 * <div <?= 'hi' ?> class="foo">
 * The most common use case for this happening is when conditional attributes are rendered within a view component
 * (which is recompiled, I should look into whether that's actually necessary)
 */
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
