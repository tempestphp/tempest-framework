<?php

namespace Tempest\View\Attributes;

use Tempest\View\Attribute;
use Tempest\View\Element;
use Tempest\View\View;

final readonly class DataAttribute implements Attribute
{
    public function __construct(
        private View $view,
        private string $name,
        private string $eval
    ) {}

    public function apply(Element $element): Element
    {
        $element->data(...[$this->name => $this->view->eval($this->eval)]);
    }
}