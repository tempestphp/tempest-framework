<?php

namespace Tempest\View\Attributes;

use Exception;
use Tempest\View\Attribute;
use Tempest\View\Element;
use Tempest\View\Elements\EmptyElement;
use Tempest\View\View;

final readonly class ElseAttribute implements Attribute
{
    public function __construct(
        private View $view,
    ) {}

    public function apply(Element $element): Element
    {
        $previous = $element->previous();

        if (!$previous) {
            throw new Exception('No previous element found for else condition');
        }

        $condition = $previous->getAttribute(':if');

        if (! $condition) {
            throw new Exception('No valid if condition found');
        }

        if ($this->view->eval($condition)) {
            return new EmptyElement($previous, $element->getAttributes());
        } else {
            return $element;
        }
    }
}