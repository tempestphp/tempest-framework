<?php

// draft

declare(strict_types=1);

namespace Tempest\View\Attributes;

use Tempest\View\Attribute;
use Tempest\View\Element;
use Tempest\View\Elements\EmptyElement;

final readonly class ElseIfAttribute implements Attribute
{
    public function apply(Element $element): Element
    {
        $previous = $element->getPrevious();

        $previousCondition = false;

        // Check all :elseif and :if conditions for previous elements
        // If one of the previous element's conditions is true, we'll stop.
        // We won't have to render this :elseif element
        while (
            $previousCondition === false
            && ($previous?->hasAttribute('if') || $previous?->hasAttribute('elseif'))
        ) {
            $previousCondition = (bool) ($previous?->getAttribute('if') ?? $previous?->getAttribute('elseif'));
            $previous = $previous->getPrevious();
        }

        $currentCondition = (bool) $element->getAttribute('elseif');

        // For this element to render, the previous conditions need to be false,
        // and the current condition must be true
        if ($previousCondition === false && $currentCondition === true) {
            return $element;
        }

        return new EmptyElement();
    }
}
