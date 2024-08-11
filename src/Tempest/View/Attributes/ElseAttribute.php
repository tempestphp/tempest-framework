<?php

declare(strict_types=1);

namespace Tempest\View\Attributes;

use Exception;
use Tempest\View\Attribute;
use Tempest\View\Element;
use Tempest\View\Elements\EmptyElement;
use Tempest\View\Elements\GenericElement;

final readonly class ElseAttribute implements Attribute
{
    public function apply(Element $element): Element
    {
        $previous = $element->getPrevious();

        if (
            ! $previous instanceof GenericElement
            || ! $previous->hasAttribute('if')
        ) {
            throw new Exception('No valid if condition found in preceding element');
        }

        $condition = $previous->getAttribute('if');

        if ($condition) {
            return new EmptyElement();
        }
        return $element;
    }
}
