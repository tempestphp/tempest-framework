<?php

namespace Tempest\View\Attributes;

use Exception;
use Tempest\View\Attribute;
use Tempest\View\Element;
use Tempest\View\Elements\EmptyElement;
use Tempest\View\HasAttributes;
use Tempest\View\View;

final readonly class ElseAttribute implements Attribute
{
    public function __construct(
        private View $view,
    ) {}

    public function apply(Element $element): Element
    {
        $previous = $element->getPrevious();

        $condition = null;

        if ($previous instanceof HasAttributes) {
            $condition = $previous->getAttribute(':if');
        }

        if (! $condition) {
            throw new Exception('No valid if condition found in preceding element');
        }

        if ($this->view->eval($condition)) {
            return new EmptyElement($element);
        } else {
            return $element;
        }
    }
}