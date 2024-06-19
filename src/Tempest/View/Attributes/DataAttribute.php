<?php

declare(strict_types=1);

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
    ) {
    }

    public function apply(Element $element): Element
    {
        $element->addData(...[$this->name => $this->view->eval($this->eval)]);
    }
}
