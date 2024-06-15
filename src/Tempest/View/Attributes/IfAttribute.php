<?php

namespace Tempest\View\Attributes;

use Tempest\View\Attribute;
use Tempest\View\Element;
use Tempest\View\Elements\EmptyElement;
use Tempest\View\View;

final readonly class IfAttribute implements Attribute
{
    public function __construct(
        private View $view,
        private string $eval,
    ) {}

    public function apply(Element $element): Element
    {
        if ($this->view->eval($this->eval)) {
            return $element;
        } else {
            return new EmptyElement($element);
        }
    }
}